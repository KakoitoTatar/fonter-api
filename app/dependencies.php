<?php
declare(strict_types=1);

use App\Application\Services\FileServiceInterface;
use App\Application\Services\MailTemplateServiceInterface;
use App\Application\Settings\SettingsInterface;
use App\Domain\Font\FontRepositoryInterface;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\User\UserRepository;
use App\Infrastructure\Commands\FontsMigrator;
use App\Infrastructure\Services\MailTemplateService;
use App\Infrastructure\Services\S3FilesystemService;
use App\Infrastructure\Validator\Rules\UniqueRule;
use Aws\S3\S3Client;
use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Application\Auth\JwtAuth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManagerInterface::class => function (ContainerInterface $c): EntityManager {
            $doctrineSettings = $c->get(SettingsInterface::class)->get('doctrine');
            $config = Setup::createAnnotationMetadataConfiguration(
                $doctrineSettings['metadata_dirs'],
                $doctrineSettings['dev_mode']
            );

            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader,
                    $doctrineSettings['metadata_dirs']
                )
            );

            $config->setMetadataCacheImpl(
                new FilesystemCache($doctrineSettings['cache_dir'])
            );

            return EntityManager::create(
                $doctrineSettings['connection'],
                $config
            );
        },
        UniqueRule::class => function (ContainerInterface $c) {
            return new UniqueRule($c->get(EntityManagerInterface::class));
        },

        // And add this entry
        JwtAuth::class => function (ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('jwt');

            $issuer = (string)$config['issuer'];
            $lifetime = (int)$config['lifetime'];
            $privateKey = (string)$config['private_key'];
            $publicKey = (string)$config['public_key'];

            return new JwtAuth($issuer, $lifetime, $privateKey, $publicKey);
        },
        Swift_Mailer::class => function (ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)['smtp_transport'];
            $transport = (new Swift_SmtpTransport($config['host'], $config['port']))
                ->setPassword($config['password'])
                ->setUsername($config['username']);

            return new Swift_Mailer($transport);
        },
        MailTemplateServiceInterface::class => function (ContainerInterface $c) {
            return new MailTemplateService();
        },
        S3Client::class => function (ContainerInterface $c) {
            return new S3Client([
                'region' => 'ru-1a',
                'version' => 'latest',
                'endpoint' => 'https://s3.selcdn.ru',
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => env('S3_ACCESS_KEY'),
                    'secret' => env('S3_SECRET_KEY')
                ]
            ]);
        },
        FileServiceInterface::class => function (ContainerInterface $c) {
            return new S3FilesystemService($c->get(S3Client::class), env('S3_BUCKET'));
        },
        Serializer::class => function (ContainerInterface $c) {
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];

            return new Serializer($normalizers, $encoders);
        },
        FontsMigrator::class => function (ContainerInterface $c) {
            return new FontsMigrator(
                $c->get(UserRepository::class),
                $c->get(FontRepositoryInterface::class),
                $c->get(MediaRepositoryInterface::class),
                $c->get(LogotypeRepositoryInterface::class),
                $c->get(FileServiceInterface::class)
            );
        }
    ]);
};
