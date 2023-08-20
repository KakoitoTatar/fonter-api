<?php

declare (strict_types=1);

namespace App\Application\Actions\Font;

use App\Application\Helpers\TagsHelper;
use App\Domain\User\User;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\DTO\Font as FontDto;

/**
 * @OA\Get(
 *     path="/api/fonts",
 *     tags={"Fonts"},
 *     @OA\Parameter(
 *         name="tags",
 *         in="query",
 *         description="tags to filter by",
 *         required=false,
 *         @OA\Schema(
 *             type="array",
 *             @OA\Items(type="string"),
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         description="part of a name to filter by",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="size",
 *         in="query",
 *         description="size of a page",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="offset",
 *         in="query",
 *         description="offset of a page",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="An OK response",
 *         @OA\JsonContent(ref="#/components/schemas/FontsCollection")
 *     )
 * ),
 * @OA\Schema(
 *     schema="FontsCollection",
 *     @OA\Property(property="offset", type="integer", description="Отступ от начала коллекции"),
 *     @OA\Property(property="size", type="integer", description="Количество объектов коллекции"),
 *     @OA\Property(property="total", type="integer", description="Всего объектов"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/Font"))
 * )
 */
class GetFonts extends FontAction
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        if (isset($data['tags'])) {
            $data['tags'] = TagsHelper::prepareTags($data['tags']);
        }

        $fonts = $this->fontRepository->readByPages(
            (int)$data['size'] ?? 20,
            (int)$data['offset'] ?? 0,
            $data['name'] ?? null,
            $data['tags'] ?? []
        );

        array_walk($fonts, function (&$item) {
            $item = FontDto::transform($item);
        });

        return $this->respondWithData(
            [
                'size' => $data['size'] ?? 20,
                'offset' => $data['offset'] ?? 0,
                'total' => $this->fontRepository->total(
                    $data['name'] ?? null,
                    $data['tags'] ?? []
                ),
                'items' => $fonts
            ]
        );
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN,
            User::USER,
            User::INACTIVE_USER,
            User::GUEST
        ];
    }

    /**
     * @return \string[][]
     */
    protected function getRules(): array
    {
        return [
            'offset' => ['numeric'],
            'size' => ['numeric'],
            'tags' => ['array'],
            'name' => []
        ];
    }
}
