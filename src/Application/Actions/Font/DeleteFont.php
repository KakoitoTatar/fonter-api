<?php

declare (strict_types=1);

namespace App\Application\Actions\Font;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

/**
 * @OA\Delete (
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
 *         @OA\Schema(
 *            type="integer"
 *        )
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Font not found",
 *         @OA\JsonContent(ref="#/components/schemas/400Response")
 *     ),
 *     @OA\Response(
 *         response="403",
 *         description="Forbidden",
 *         @OA\JsonContent(ref="#/components/schemas/403Response")
 *     ),
 *     @OA\Response(
 *         response="204",
 *         description="An OK response"
 *     ),
 *     @OA\Response(
 *         response="500",
 *         description="Some error when deleting font"
 *     )
 * )
 */
class DeleteFont extends FontAction
{
    /**
     * @return Response
     * @throws HttpForbiddenException
     */
    protected function action(): Response
    {
        $id = $this->request->getParsedBody()['id'];
        $font = $this->fontRepository->read($id);
        /** @var User $user */
        $user = $this->request->getAttribute('user')->getRole();

        if ($user->getRole() !== User::ADMIN
            || $user->getId() === $font->getAuthor()->getId()) {
            throw new HttpForbiddenException($this->request);
        }

        $font->getFile()->setTemporal(true);

        $isDelete = $this->fontRepository->delete($id);

        return $this->response->withStatus($isDelete ? 204 : 500);
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
            'id' => ['integer', 'required']
        ];
    }
}
