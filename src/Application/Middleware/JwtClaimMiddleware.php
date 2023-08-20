<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Application\Auth\JwtAuth;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JwtClaimMiddleware implements MiddlewareInterface
{
    /**
     * @var JwtAuth
     */
    private JwtAuth $jwtAuth;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @param JwtAuth $jwtAuth The JWT auth
     * @param UserRepository $userRepository
     */
    public function __construct(JwtAuth $jwtAuth, UserRepository $userRepository)
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->jwtAuth = $jwtAuth;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \App\Domain\User\UserNotFoundException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $credentials = $request->getHeader('Authorization')[0] ?? null;

        if (
            $credentials
            && ($credentials = explode(' ', $credentials)[1])
            && $this->jwtAuth->validateToken($credentials)
        ) {
            // Append valid token
            $parsedToken = $this->jwtAuth->createParsedToken($credentials);
            $request = $request->withAttribute('token', $parsedToken);

            // Append the user id as request attribute;
            $user = $this->userRepository->find($parsedToken->claims()->all()['id']);

            $request = $request->withAttribute('user', $user);
        } else {
            $user = new User();
            $user->setRole(User::GUEST);
            $request = $request->withAttribute('user', $user);
        }

        return $handler->handle($request);
    }
}