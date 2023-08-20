<?php

declare (strict_types=1);

namespace App\Application\Actions\Logotype;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class LogotypeAction extends Action
{
    /**
     * @var LogotypeRepositoryInterface
     */
    protected LogotypeRepositoryInterface $logotypeRepository;

    /**
     * LogotypeAction constructor.
     * @param LoggerInterface $logger
     * @param LogotypeRepositoryInterface $logotypeRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        LogotypeRepositoryInterface $logotypeRepository,
        Serializer $serializer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($logger, $serializer, $validator);
        $this->logotypeRepository = $logotypeRepository;
    }
}
