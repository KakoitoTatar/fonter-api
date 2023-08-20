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
use Slim\Exception\HttpForbiddenException;
use Symfony\Component\Serializer\Serializer;
use App\Application\DTO\Logotype as LogotypeDto;

/**
 * @OA\Patch(
 *     path="/api/logotypes/{id}",
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
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Id логотипа",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="tags",
 *         in="query",
 *         required=true,
 *         description="Тэги для логотипа",
 *         @OA\Schema(
 *            type="array",
 *            @OA\Items
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="file",
 *         in="query",
 *         required=false,
 *         description="Id файла логотипа",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="cover",
 *         in="query",
 *         required=false,
 *         description="Id файла обложки логотипа",
 *         @OA\Schema(
 *            type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         required=false,
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
 *         response="404",
 *         description="Logotype not found",
 *         @OA\JsonContent(ref="#/components/schemas/400Response")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/Logotype")
 *     )
 * )
 */
class PatchLogotype extends LogotypeAction
{
    /**
     * @var MediaRepositoryInterface
     */
    private MediaRepositoryInterface $mediaRepository;

    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileRepository;

    /**
     * PatchLogotype constructor.
     * @param LoggerInterface $logger
     * @param LogotypeRepositoryInterface $logotypeRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param MediaRepositoryInterface $mediaRepository
     * @param FileServiceInterface $s3FilesystemService
     */
    public function __construct(
        LoggerInterface $logger,
        LogotypeRepositoryInterface $logotypeRepository,
        Serializer $serializer,
        ValidatorInterface $validator,
        MediaRepositoryInterface $mediaRepository,
        FileServiceInterface $s3FilesystemService
    ) {
        parent::__construct($logger, $logotypeRepository, $serializer, $validator);
        $this->mediaRepository = $mediaRepository;
        $this->fileRepository = $s3FilesystemService;
    }

    /**
     * @return Response
     * @throws HttpForbiddenException
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();
        $cover = null;
        $file = null;

        $oldLogotypeModel = $this->logotypeRepository->read($data['id']);

        if ($this->request->getAttribute('user')->getRole() !== User::ADMIN
            || $oldLogotypeModel->getAuthor()->getId() !== $this->request->getAttribute('user')->getId()) {
            throw new HttpForbiddenException($this->request);
        }

        if (isset($data['cover'])) {
            $cover = $this->mediaRepository->find($data['cover']);
            $cover->setTemporal(false);

            $sourceKeyFile = $cover->getUrl();

            $namePieces = explode('.', $sourceKeyFile);
            $extension = array_pop($namePieces);

            $destinationKey = 'logotypes/' . $oldLogotypeModel->getId() . '/cover.' . $extension;

            $this->fileRepository->move($sourceKeyFile, $destinationKey);

            $cover->setUrl($destinationKey);

            $oldLogotypeModel->getCover()->setTemporal(true);
        }

        if (isset($data['file'])) {
            $file = $this->mediaRepository->find($data['file']);
            $file->setTemporal(false);

            $sourceKeyFile = $file->getUrl();

            $namePieces = explode('.', $sourceKeyFile);
            $extension = array_pop($namePieces);

            $destinationKey = 'logotypes/' . $oldLogotypeModel->getId() . '/cover.' . $extension;

            $this->fileRepository->move($sourceKeyFile, $destinationKey);

            $file->setUrl($destinationKey);

            $oldLogotypeModel->getFile()->setTemporal(true);
        }

        $logotype = $this->logotypeRepository->update(
            $data['id'],
            $data['name'] ?? null,
            $file,
            $cover,
            $data['tags'] ?? null
        );

        return $this->respondWithData(LogotypeDto::transform($logotype));
    }

    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN,
            User::USER
        ];
    }

    protected function getRules(): array
    {
        return [
            'id' => ['integer', 'required'],
            'name' => ['string'],
            'file' => ['integer', ['exists', Media::class]],
            'cover' => ['integer', ['exists', Media::class]],
            'tags' => ['array', 'required']
        ];
    }
}