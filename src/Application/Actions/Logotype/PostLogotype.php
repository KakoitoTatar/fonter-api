<?php

declare (strict_types=1);

namespace App\Application\Actions\Logotype;

use App\Application\Services\FileServiceInterface;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use App\Application\DTO\Logotype as LogotypeDto;

/**
 * @OA\Post(
 *     path="/api/logotypes",
 *     tags={"Logotypes"},
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
 *         description="Id файла логотипа",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="cover",
 *         in="query",
 *         required=true,
 *         description="Id файла обложки логотипа",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         required=true,
 *         description="Название логотипа",
 *         @OA\Schema(
 *            type="string"
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
 *         @OA\JsonContent(ref="#/components/schemas/Logotype")
 *     )
 * )
 */
class PostLogotype extends LogotypeAction
{
    /**
     * @var MediaRepositoryInterface
     */
    private MediaRepositoryInterface $mediaRepository;

    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $filesystemService;

    /**
     * PostLogotype constructor.
     * @param LoggerInterface $logger
     * @param LogotypeRepositoryInterface $logotypeRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param MediaRepositoryInterface $mediaRepository
     * @param FileServiceInterface $filesystemService
     */
    public function __construct(
        LoggerInterface $logger,
        LogotypeRepositoryInterface $logotypeRepository,
        Serializer $serializer,
        ValidatorInterface $validator,
        MediaRepositoryInterface $mediaRepository,
        FileServiceInterface $filesystemService
    ) {
        parent::__construct($logger, $logotypeRepository, $serializer, $validator);
        $this->mediaRepository = $mediaRepository;
        $this->filesystemService = $filesystemService;
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        $cover = $this->mediaRepository->find($data['cover']);
        $cover->setTemporal(false);

        $file = $this->mediaRepository->find($data['file']);
        $file->setTemporal(false);

        $logotype = $this->logotypeRepository->create(
            $data['name'],
            $file,
            $cover,
            $this->request->getAttribute('user'),
            $data['tags']
        );

        /**
         * @var string $key
         * @var Media $media
         */
        foreach (['cover' => $cover, 'font' => $file] as $key => $media) {
            $sourceKeyFile = $logotype->getFile()->getUrl();

            $namePieces = explode('.', $sourceKeyFile);
            $extension = array_pop($namePieces);

            $destinationKey = 'logotypes/' . $logotype->getId() . '/' . $key . '.' . $extension;

            $this->filesystemService->move($sourceKeyFile, $destinationKey);

            $this->mediaRepository->update($media->getId(), $destinationKey);
        }

        return $this->respondWithData(LogotypeDto::transform($logotype));
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN,
            User::USER,
        ];
    }

    protected function getRules(): array
    {
        return [
            'name' => ['string', 'required'],
            'file' => ['integer', 'required', ['exists', Media::class]],
            'cover' => ['integer', 'required', ['exists', Media::class]],
            'tags' => ['array', 'required']
        ];
    }
}