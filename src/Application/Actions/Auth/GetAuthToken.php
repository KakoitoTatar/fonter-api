<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Domain\User\User;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpUnauthorizedException;

/**
 * @OA\Post(
 *     path="/api/auth",
 *     tags={"Auth"},
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *      ),
 *      @OA\Parameter(
 *          name="password",
 *          in="query",
 *          required=true,
 *          @OA\Schema(
 *             type="string",
 *             maxLength=64,
 *             minLength=8
 *         )
 *      ),
 *      @OA\Response(
 *          response="200",
 *          description="an OK response",
 *          @OA\JsonContent(ref="#/components/schemas/Auth200Response")
 *      ),
 *      @OA\Response(
 *          response="401",
 *          description="Wrong Login/Password response",
 *          @OA\JsonContent(ref="#/components/schemas/400Response")
 *      ),
 *      @OA\Response(
 *          response="400",
 *          description="Validation errors response",
 *          @OA\JsonContent(ref="#/components/schemas/Validation400Response")
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Wrong access rights exception",
 *          @OA\JsonContent(ref="#/components/schemas/403Response")
 *      )
 * )
 * @OA\Schema(
 *     schema="Auth200Response",
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="token", type="string")
 * )
 * @OA\Schema(
 *     schema="400Response",
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="description", type="string")
 * )
 *  * @OA\Schema(
 *     schema="Validation400Response",
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(
 *         property="description",
 *         description="Описание ошибки",
 *         type="array", @OA\Items(
 *              @OA\Property(property="Field name example", description="Пример поля", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
class GetAuthToken extends AuthAction
{
    /**
     * @return Response
     * @throws HttpUnauthorizedException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function action(): Response
    {
        $data = (array)$this->request->getParsedBody();

        $email = (string)$data['email'];
        $password = (string)$data['password'];

        /**
         * @var User $user
         */
        $user = $this->userRepository->getActiveUserByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            // Invalid authentication credentials
            throw new HttpUnauthorizedException($this->request, 'Неверный логин или пароль');
        }

        $user->setLastLogin(new \DateTime());
        $this->em->flush();

        // Create a fresh token
        $token = $this->jwtAuth->createJwt([
            'id' => $user->getId()
        ]);

        $result = [
            'message' => 'Авторизация прошла успешно',
            'token' => $token
        ];

        return $this->respondWithData($result, 201);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::GUEST
        ];
    }

    /**
     * @return string[]
     */
    protected function getRules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', ['lengthBetween', 8, 64]],
        ];
    }
}
