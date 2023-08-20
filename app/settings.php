<?php
declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Commands\FontsMigrator;
use App\Infrastructure\Commands\SendMessages;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            $connection = include 'connections.php';
            return new Settings([
                'determineRouteBeforeAppMiddleware' => true,
                'baseUrl' => 'http://localhost',
                'displayErrorDetails' => true, // Should be set to false in production
                'logger' => [
                    'name' => 'monolog',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    // if true, metadata caching is forcefully disabled
                    'dev_mode' => true,

                    // path where the compiled metadata info will be cached
                    // make sure the path exists and it is writable
                    'cache_dir' => __DIR__ . '/../var/doctrine',

                    // you should add any other path containing annotated entity classes
                    'metadata_dirs' => [__DIR__ . '/../src/Domain'],

                    'connection' => $connection['db']
                ],
                'jwt' => [
                    // The issuer name
                    'issuer' => env('JWT_ISSUER'),

                    // Max lifetime in seconds
                    'lifetime' => env('JWT_LIFETIME'),

                    // The private key
                    'private_key' => env('JWT_PRIVATE'),

                    'public_key' => env('JWT_PUBLIC'),
                ],
                'smtp_transport' => [
                    'host' => env('SMTP_HOST'),
                    'username' => env('SMTP_USERNAME'),
                    'password' => env('SMTP_PASSWORD'),
                    'port' => env('SMTP_PORT'),
                    'encryption' => 'tls',
                ],
                'email' => [
                    'no-reply' => 'no-reply@friday-drop.media'
                ],
                'commands' => [
                    SendMessages::class,
                    FontsMigrator::class
                ]
            ]);
        }
    ]);
};
