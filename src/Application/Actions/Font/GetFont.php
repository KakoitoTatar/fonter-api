<?php

declare (strict_types=1);

namespace App\Application\Actions\Font;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\DTO\Font as FontDto;

/**
 * @OA\Get(
 *     path="/api/fonts/{id}",
 *     tags={"Fonts"},
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
 *          description="Font not found",
 *          @OA\JsonContent(ref="#/components/schemas/400Response")
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Font not found",
 *          @OA\JsonContent(ref="#/components/schemas/403Response")
 *      ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/Font")
 *     )
 * )
 */
class GetFont extends FontAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $font = $this->fontRepository
            ->read((int) $this->request->getParsedBody()['id']);

        return $this->respondWithData(FontDto::transform($font));
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::GUEST,
            User::INACTIVE_USER,
            User::USER,
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
