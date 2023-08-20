<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\User\UserRepository;
use App\Infrastructure\Validator\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class UserAction extends Action
{
    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * @param LoggerInterface $logger
     * @param UserRepository $userRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository,
        Serializer $serializer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($logger, $serializer, $validator);
        $this->userRepository = $userRepository;
    }
}
