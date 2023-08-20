<?php

declare(strict_types=1);

namespace App\Application\Actions\Media;

use App\Application\Actions\Action;
use App\Application\Services\FileServiceInterface;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Media\MediaRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class MediaAction extends Action
{
    /**
     * @var MediaRepositoryInterface
     */
    protected MediaRepositoryInterface $repository;

    /**
     * @var FileServiceInterface
     */
    protected FileServiceInterface $fileService;

    /**
     * MediaAction constructor.
     * @param LoggerInterface $logger
     * @param MediaRepositoryInterface $repository
     * @param FileServiceInterface $fileService
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        MediaRepositoryInterface $repository,
        FileServiceInterface $fileService,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $serializer, $validator);
        $this->repository = $repository;
        $this->fileService = $fileService;
    }
}