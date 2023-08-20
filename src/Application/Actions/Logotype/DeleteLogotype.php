<?php

declare (strict_types=1);

namespace App\Application\Actions\Logotype;

use App\Domain\User\User;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

/**
 * @OA\Delete (
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
 *         @OA\Schema(
 *            type="integer"
 *        )
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Logotype not found",
 *         @OA\JsonContent(ref="#/components/schemas/400Response")
 *     ),
 *     @OA\Response(
 *         response="204",
 *         description="An OK response"
 *     ),
 *     @OA\Response(
 *         response="500",
 *         description="Some error when deleting font"
 *     ),
 *     @OA\Response(
 *         response="403",
 *         description="Forbidden",
 *         @OA\JsonContent(ref="#/components/schemas/403Response")
 *     )
 * )
 */
class DeleteLogotype extends LogotypeAction
{
    /**
     * @return Response
     * @throws HttpForbiddenException
     */
    protected function action(): Response
    {
        $id = $this->request->getParsedBody()['id'];

        $logotype = $this->logotypeRepository->read($id);

        if ($this->request->getAttribute('user')->getRole() !== User::ADMIN
            || $logotype->getAuthor()->getId() !== $this->request->getAttribute('user')->getId()) {
            throw new HttpForbiddenException($this->request);
        }

        $logotype->getFile()->setTemporal(true);
        $logotype->getCover()->setTemporal(true);

        $isDelete = $this->logotypeRepository->delete($id);

        return $this->response->withStatus($isDelete ? 204 : 500);
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
     * @return \string[][]
     */
    protected function getRules(): array
    {
        return [
            'id' => ['integer', 'required']
        ];
    }
}
