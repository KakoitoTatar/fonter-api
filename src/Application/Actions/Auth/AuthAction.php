<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Auth\JwtAuth;
use App\Application\Validator\ValidatorInterface;
use App\Domain\User\UserRepository as UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AuthAction extends Action
{
    /**
     * @var JwtAuth
     */
    protected JwtAuth $jwtAuth;

    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * AuthAction constructor.
     * @param LoggerInterface $logger
     * @param JwtAuth $jwtAuth
     * @param UserRepositoryInterface $userRepository
     * @param EntityManagerInterface $em
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        JwtAuth $jwtAuth,
        UserRepositoryInterface $userRepository,
        EntityManagerInterface $em,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $serializer, $validator);
        $this->jwtAuth = $jwtAuth;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }
}
