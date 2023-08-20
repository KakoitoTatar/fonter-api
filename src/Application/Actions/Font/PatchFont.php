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
 * @OA\Patch (
 *     path="/api/fonts/{id}",
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
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Id шрифта",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="tags",
 *         in="query",
 *         required=false,
 *         description="Тэги для шрифта",
 *         @OA\Schema(
 *            type="array",
 *            @OA\Items
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="file",
 *         in="query",
 *         required=false,
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
 *         response="404",
 *         description="Font not found",
 *         @OA\JsonContent(ref="#/components/schemas/400Response")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/Font")
 *     )
 * )
 */
class PatchFont extends FontAction
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
     * PatchFont constructor.
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
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->fileService = $s3FilesystemService;
        parent::__construct($logger, $fontRepository, $serializer, $validator);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN,
            User::USER
        ];
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'id' => ['integer', 'required'],
            'tags' => ['array'],
            'file' => ['integer']
        ];
    }

    protected function action(): Response
    {
        $data = $this->request->getParsedBody();
        $font = $this->fontRepository->read($data['id']);

        $newFile = null;
        $fileName = null;
        if ($data['file'] !== null) {
            $newFile = $this->mediaRepository->find($data['file']);

            $namePieces = explode('/', $newFile->getUrl());
            $fileNameWithSeed = array_pop($namePieces);
            $fileNameWithSeedPieces = explode('_', $fileNameWithSeed);
            array_shift($fileNameWithSeedPieces);
            $fileName = implode('_', $fileNameWithSeedPieces);

            $destinationKey = 'fonts/' . $font->getId() . '/' . $fileName;

            $this->fileService->move($newFile->getUrl(), $destinationKey);

            $newFile->setTemporal(false);
            $newFile->setUrl($destinationKey);
        }

        $this->fontRepository->update($data['id'], $fileName, $data['tags'], $newFile);

        return $this->respondWithData(FontDto::transform($font));
    }
}
