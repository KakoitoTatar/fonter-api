<?php

declare(strict_types=1);

namespace App\Application\Actions\Media;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\UploadedFile;

/**
 * @OA\Post(
 *     path="/api/media",
 *     tags={"Media"},
 *     @OA\Parameter(
 *         name="rootDirectory",
 *         in="query",
 *         required=true,
 *         @OA\Schema(
 *            type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="media",
 *         in="query",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/PostMediaResponse")
 *     )
 * )
 * @OA\Schema(
 *     schema="PostMediaResponse",
 *     @OA\Property(property="id", type="integer")
 * )
 * @OA\Schema(
 *     schema="PostMediaRequest",
 *     @OA\Property(property="id", type="integer")
 * )
 */
class PostMedia extends MediaAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();
        /**
         * @var $file UploadedFile
         */
        $file = $this->request->getUploadedFiles()['media'];

        $identifier = $data['rootDirectory'] . '/' . uniqid('', true) . '_' . urlencode($file->getClientFilename());

        $media = $this->repository->findOneBy(['url' => $identifier]);

        if ($media !== null) {
            return $this->respondWithData($media, 200);
        }

        $this->fileService->put($identifier, $file);

        $media = $this->repository->save($identifier);

        return $this->respondWithData(['id' => $media->getId()]);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN
        ];
    }

    /**
     * @return string[]
     */
    protected function getRules(): array
    {
        return [
            'rootDirectory' => ['required'],
            'media' => ['required', ['uploadedFile']]
        ];
    }
}
