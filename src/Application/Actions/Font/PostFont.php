<?php

declare (strict_types=1);

namespace App\Application\Actions\Font;

use App\Application\Services\FileServiceInterface;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Font\FontRepositoryInterface;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use App\Application\DTO\Font as FontDto;

/**
 * @OA\Post(
 *     path="/api/fonts",
 *     tags={"Fonts"},
 *     @OA\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         description="Bearer JWT token",
 *         example="Bearer token",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="tags",
 *         in="query",
 *         required=true,
 *         description="Тэги для шрифта",
 *         @OA\Schema(
 *            type="array",
 *            @OA\Items
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="file",
 *         in="query",
 *         required=true,
 *         description="Id файла для шрифта",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response="403",
 *         description="Forbidden",
 *         @OA\JsonContent(ref="#/components/schemas/403Response")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/Font")
 *     )
 * )
 */
class PostFont extends FontAction
{
    /**
     * @var MediaRepositoryInterface
     */
    private MediaRepositoryInterface $mediaRepository;

    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileService;

    /**
     * PostFont constructor.
     * @param LoggerInterface $logger
     * @param FontRepositoryInterface $fontRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param MediaRepositoryInterface $mediaRepository
     * @param FileServiceInterface $s3FilesystemService
     */
    public function __construct(
        LoggerInterface $logger,
        FontRepositoryInterface $fontRepository,
        Serializer $serializer,
        ValidatorInterface $validator,
        MediaRepositoryInterface $mediaRepository,
        FileServiceInterface $s3FilesystemService
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->fileService = $s3FilesystemService;
        parent::__construct($logger, $fontRepository, $serializer, $validator);
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();
        $media = $this->mediaRepository->find($data['file']);

        $namePieces = explode('/', $media->getUrl());
        $fileNameWithSeed = array_pop($namePieces);
        $fileNameWithSeedPieces = explode('_', $fileNameWithSeed);
        array_shift($fileNameWithSeedPieces);
        $fileName = implode('_', $fileNameWithSeedPieces);

        $media->setTemporal(false);

        $font = $this->fontRepository->create(
            $fileName,
            $data['tags'],
            $this->request->getAttribute('user'),
            $media
        );

        $sourceKeyFile = $font->getFile()->getUrl();

        $destinationKey = 'fonts/' . $font->getId() . '/' . $fileName;

        $this->fileService->move($sourceKeyFile, $destinationKey);

        $this->mediaRepository->update($media->getId(), $destinationKey);

        return $this->respondWithData(FontDto::transform($font));
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::USER,
            User::ADMIN
        ];
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'tags' => ['array', 'required'],
            'file' => ['integer', 'required']
        ];
    }
}
