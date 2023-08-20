<?php

declare (strict_types=1);

namespace App\Infrastructure\Commands;

use App\Application\Services\FileServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use App\Domain\Font\FontRepositoryInterface;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use GuzzleHttp\Client;
use Slim\Psr7\UploadedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FontsMigrator extends Command
{
    /**
     * @var array
     */
    private const ROLES_MAP = [
        'admin' => User::ADMIN,
        'author' => User::USER
    ];

    /**
     * @var string
     */
    protected static $defaultName = 'oldsite:migrate';

    /**
     * @var array
     */
    protected array $settings;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var FontRepositoryInterface
     */
    private FontRepositoryInterface $fontRepository;

    /**
     * @var MediaRepositoryInterface
     */
    private MediaRepositoryInterface $mediaRepository;

    /**
     * @var LogotypeRepositoryInterface
     */
    private LogotypeRepositoryInterface $logotypeRepository;

    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileService;

    /**
     * @var Client
     */
    private Client $yandexClient;

    /**
     * SendMessages constructor.
     * @param string|null $name
     */
    public function __construct(
        UserRepository              $userRepository,
        FontRepositoryInterface     $fontRepository,
        MediaRepositoryInterface    $mediaRepository,
        LogotypeRepositoryInterface $logotypeRepository,
        FileServiceInterface        $fileService,
        string                      $name = null
    ) {
        $this->userRepository = $userRepository;
        $this->fontRepository = $fontRepository;
        $this->mediaRepository = $mediaRepository;
        $this->logotypeRepository = $logotypeRepository;
        $this->fileService = $fileService;
        parent::__construct($name);
        $this->yandexClient = new Client(['base_uri' => 'https://cloud-api.yandex.net']);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Миграция данных со старого сайта');
        $this->setHelp('Миграция данных со старого сайта');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputStyle = new OutputFormatterStyle('white', 'blue', ['bold', 'blink']);
        $output->getFormatter()->setStyle('fire', $outputStyle);

        $oldFonterConnection = new DB(
            'mysql:host=comicsdb.ru:3306;dbname=fonter',
            'root',
            'w12f23un3'
        );

        $users = $oldFonterConnection->query(
            'SELECT
                u.id as id,
                u.username as username,
                u.email as email,
                u.password_hash as password,
                aa.item_name as role
            FROM users as u
            JOIN auth_assignment as aa
            ON aa.user_id=u.id
            WHERE u.status=10;'
        );

        $progressBar = new ProgressBar($output, $users->rowCount());
        $progressBar->start();

        $users = $users->fetchAll();

        $oldFonterConnection = null;

        foreach ($users as $userItem) {
            $user = $this->userRepository->findOneBy(['email' => $userItem['email']]);
            if ($user === null) {
                $user = new User();
                $user->setRawPassword($userItem['password']);
                $user->setEmail($userItem['email']);
                $user->setRole(self::ROLES_MAP[$userItem['role']]);
                $user->setLastLogin(new \DateTime());
                $this->userRepository->save($user);
            }

            $this->moveFonts((int) $userItem['id'], $progressBar, $user);

            $this->moveLogotypes((int)$userItem['id'], $progressBar, $user);

            $progressBar->advance();
            $output->writeln('');
        }
        $progressBar->finish();
        return Command::SUCCESS;
    }

    /**
     * @param PDO $oldFonterConnection
     * @param int $userId
     * @param ProgressBar $progressBar
     * @param User $user
     * @return void
     */
    private function moveFonts(
        int $userId,
        ProgressBar $progressBar,
        User $user
    ): void {
        $oldFonterConnection = new DB(
            'mysql:host=comicsdb.ru:3306;dbname=fonter',
            'root',
            'w12f23un3'
        );
        $fonts = $oldFonterConnection->query('SELECT * FROM fonts WHERE author=' . $userId);
        $progressBar->setMaxSteps($progressBar->getMaxSteps() + $fonts->rowCount());
        $fonts = $fonts->fetchAll();
        $oldFonterConnection = null;

        foreach ($fonts as $fontItem) {
            $font = $this->fontRepository->findOneBy([
                'name' => $fontItem['name'],
                'author' => $user,
                'tags' => $fontItem['type']
            ]);
            if ($font === null) {
                $clearFontName = str_replace('/', '_', $fontItem['name'])
                    . '.' . explode('.', $fontItem['link'])[1];

                $fileUrl = 'https://scanlate.ru/download?'
                    . http_build_query(['id' => (int) $fontItem['id'], 'type' => 'font']);
                $fileName = './tmp/fonts/' . $clearFontName;
                $media = $this->saveRemoteFileAsMediaObject($fileName, $fileUrl);

                $font = $this->fontRepository->create(
                    $fontItem['name'],
                    explode(',', $fontItem['type']),
                    $user,
                    $media
                );

                $fileNamePieces = explode('/', $font->getFile()->getUrl());
                $fileName = array_pop($fileNamePieces);
                $this->moveMediaToCorrectDir(
                    $font->getFile()->getId(),
                    $font->getFile()->getUrl(),
                    'fonts/' . $font->getId() . '/' . $fileName
                );
            }

            $progressBar->advance();
        }
    }

    /**
     * @param PDO $oldFonterConnection
     * @param int $userId
     * @param ProgressBar $progressBar
     * @param User $user
     * @return void
     * @throws GuzzleException
     */
    private function moveLogotypes(
        int $userId,
        ProgressBar $progressBar,
        User $user
    ) {
        $oldFonterConnection = new DB(
            'mysql:host=comicsdb.ru:3306;dbname=fonter',
            'root',
            'w12f23un3'
        );
        $logotypes = $oldFonterConnection->query('SELECT * FROM logotypes WHERE author=' . $userId);
        $progressBar->setMaxSteps($progressBar->getMaxSteps() + $logotypes->rowCount());
        $logotypes = $logotypes->fetchAll();
        $oldFonterConnection = null;

        foreach ($logotypes as $logotypeItem) {

            $logotype = $this->logotypeRepository->findOneBy([
                'name' => $logotypeItem['name'],
                'author' => $user,
                'tags' => $logotypeItem['type']
            ]);

            if ($logotype === null) {
                $clearLogotypeName = str_replace('/', '_', $logotypeItem['name'])
                    . '.' . explode('.', $logotypeItem['image'])[1];

                $logotypeCoverName = './tmp/logos/'
                    . ('Cover_' . $clearLogotypeName);
                $logotypeCoverUrl = 'https://scanlate.ru/web/logos/' . $logotypeItem['image'];
                $logotypeCoverMedia = $this->saveRemoteFileAsMediaObject($logotypeCoverName, $logotypeCoverUrl);

                $logotypeMedia = $this->saveLogotypeFromYandex(
                    str_replace('/', '_', $logotypeItem['name']),
                    $logotypeItem['link']
                );

                $logotype = $this->logotypeRepository->create(
                    $logotypeItem['name'],
                    $logotypeMedia,
                    $logotypeCoverMedia,
                    $user,
                    explode(',', $logotypeItem['type'])
                );

                $fileNamePieces = explode('/', $logotype->getFile()->getUrl());
                $fileName = array_pop($fileNamePieces);
                $this->moveMediaToCorrectDir(
                    $logotype->getFile()->getId(),
                    $logotype->getFile()->getUrl(),
                    'logotypes/' . $logotype->getId() . '/' . $fileName
                );

                $fileNamePieces = explode('/', $logotype->getCover()->getUrl());
                $fileName = array_pop($fileNamePieces);
                $this->moveMediaToCorrectDir(
                    $logotype->getCover()->getId(),
                    $logotype->getCover()->getUrl(),
                    'logotypes/' . $logotype->getId() . '/' . $fileName
                );
            }

            $progressBar->advance();
        }
    }

    /**
     * @param string $fileName
     * @param string $fileUrl
     * @return Media
     * @throws GuzzleException
     * @throws \JsonException
     */
    private function saveLogotypeFromYandex(string $clearName, string $fileUrl): Media
    {
        $yaResponse = $this->yandexClient->get('/v1/disk/public/resources/download?' . http_build_query([
                'public_key' => $fileUrl
            ]), [
            'headers' => ['Authorization' => 'OAuth AQAAAAAJsNBXAAZfa3CwExdyX035mhEmZb1EFKc']
        ])->getBody()->getContents();

        $link = json_decode($yaResponse, true, 512, JSON_THROW_ON_ERROR)['href'];

        $queryPart = parse_url($link, PHP_URL_QUERY);
        parse_str($queryPart, $queryPieces);
        $fileNamePieces = explode('.', $queryPieces['filename']);
        $fileExtension = array_pop($fileNamePieces);

        return $this->saveRemoteFileAsMediaObject('./tmp/logos/' . $clearName . '.' . $fileExtension, $link);
    }

    /**
     * @param string $fileName
     * @param string $fileUrl
     * @return Media
     */
    private function saveRemoteFileAsMediaObject(string $fileName, string $fileUrl): Media
    {
        file_put_contents($fileName, file_get_contents($fileUrl));

        $uploadedFile = new UploadedFile(
            $fileName,
            substr($fileName, 12),
            mime_content_type($fileName),
            filesize($fileName),
            0
        );

        $this->fileService->put(
            $fileName,
            $uploadedFile
        );

        unlink($fileName);

        return $this->mediaRepository->save(substr($fileName, 2));
    }

    /**
     * @param int $mediaId
     * @param string $sourceKey
     * @param string $destinationKey
     * @return void
     */
    private function moveMediaToCorrectDir(int $mediaId, string $sourceKey, string $destinationKey): void
    {
        $this->fileService->move($sourceKey, $destinationKey);

        $this->mediaRepository->update($mediaId, $destinationKey);
    }
}
