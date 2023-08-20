<?php

declare (strict_types=1);

namespace App\Application\Actions\Font;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Font\FontRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class FontAction extends Action
{
    /**
     * @var FontRepositoryInterface
     */
    protected FontRepositoryInterface $fontRepository;

    /**
     * LogotypeAction constructor.
     * @param LoggerInterface $logger
     * @param FontRepositoryInterface $fontRepository
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        FontRepositoryInterface $fontRepository,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $serializer, $validator);
        $this->fontRepository = $fontRepository;
    }
}