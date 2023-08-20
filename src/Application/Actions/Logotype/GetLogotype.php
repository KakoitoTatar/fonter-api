<?php

declare (strict_types=1);

namespace App\Application\Actions\Logotype;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\DTO\Logotype as LogotypeDto;

/**
 * @OA\Get(
 *     path="/api/logotypes/{id}",
 *     tags={"Logotypes"},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          @OA\Schema(
 *             type="integer"
 *         )
 *      ),
 *      @OA\Response(
 *          response="404",
 *          description="Logotype not found",
 *          @OA\JsonContent(ref="#/components/schemas/400Response")
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Logotype not found",
 *          @OA\JsonContent(ref="#/components/schemas/403Response")
 *      ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/Logotype")
 *     )
 * )
 */
class GetLogotype extends LogotypeAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $id = $this->request->getParsedBody()['id'];

        $logotype = $this->logotypeRepository->read($id);

        return $this->respondWithData(LogotypeDto::transform($logotype));
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

    protected function getRules(): array
    {
        return [
            'id' => ['integer', 'required']
        ];
    }
}