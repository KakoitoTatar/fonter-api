<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\DTO\User as UserDto;

/**
 * @OA\Get(
 *      path="/api/auth",
 *     tags={"Users"},
 *      @OA\Parameter(
 *          name="Authorization",
 *          in="header",
 *          required=true,
 *          description="Bearer JWT token",
 *          example="Bearer token",
 *         @OA\Schema(
 *             type="string"
 *         )
 *      ),
 *      @OA\Response(
 *          response="200",
 *          description="an OK response",
 *          @OA\JsonContent(ref="#/components/schemas/User200Response")
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Wrong access rights exception",
 *          @OA\JsonContent(ref="#/components/schemas/403Response")
 *      )
 * )
 * @OA\Schema(
 *     schema="User200Response",
 *     @OA\Property(property="id", type="int"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="role", type="string")
 * ),
 * @OA\Schema(
 *     schema="403Response",
 *     @OA\Property(property="type", type="string", example="Forbidden."),
 *     @OA\Property(property="description", type="string", example="You are not permitted to perform the requested operation."),
 *     @OA\Property(property="trace", type="string", example=null)
 * )
 */
class GetAuthenticatedUser extends UserAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        return $this->respondWithData(
            UserDto::transform(
                $this->request->getAttribute('user')
            )
        );
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
        return [];
    }
}
