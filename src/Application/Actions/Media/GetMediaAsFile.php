<?php

declare (strict_types=1);

namespace App\Application\Actions\Media;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @OA\Get(
 *     path="/api/download/{url}",
 *     tags={"Media"},
 *     @OA\Parameter(
 *         name="url",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *            type="string"
 *        )
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Media not found",
 *         @OA\JsonContent(ref="#/components/schemas/400Response")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string")),
 *         @OA\Header(header="Content-Type", @OA\Schema(type="string")),
 *         @OA\Header(header="Content-Length", @OA\Schema(type="integer")),
 *         @OA\MediaType(mediaType="", @OA\Schema(type="string", format="binary"))
 *     )
 * )
 */
class GetMediaAsFile extends MediaAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $url = $this->request->getParsedBody()['url'];

        $media = $this->repository->findOneBy(['url' => $url]);

        $file = $this->fileService->get($media->getBucket(), $media->getUrl());

        return $this->response->withBody($file->getStream())
            ->withHeader('Content-Disposition', $media->getUrl())
            ->withHeader('Content-Length', $file->getSize())
            ->withHeader('Content-Type', $file->getClientMediaType());
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::USER,
            User::ADMIN,
            User::GUEST,
            User::INACTIVE_USER
        ];
    }

    /**
     * @return string[][]
     */
    protected function getRules(): array
    {
        return [
            'url' => ['required']
        ];
    }
}