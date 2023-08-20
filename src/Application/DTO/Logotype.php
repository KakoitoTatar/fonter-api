<?php

declare (strict_types=1);

namespace App\Application\DTO;

/**
 * @OA\Schema
 */
class Logotype
{
    /**
     * @OA\Property
     * @var int|null
     */
    protected ?int $id;

    /**
     * @OA\Property
     * @var string
     */
    protected string $name;

    /**
     * @OA\Property
     * @var string
     */
    protected string $file;

    /**
     * @OA\Property
     * @var string
     */
    protected string $cover;

    /**
     * @OA\Property(@OA\Items)
     * @var array
     */
    protected array $tags;

    /**
     * Logotype constructor.
     */
    private function __construct()
    {
        /**
         * Disable DTO's construct
         */
    }

    /**
     * @param \App\Domain\Logotype\Logotype $logotype
     * @return Logotype
     */
    public static function transform(\App\Domain\Logotype\Logotype $logotype): Logotype
    {
        $dto = new self();

        $dto->file = '/files/' . $logotype->getFile()->getBucket() . '/' . $logotype->getFile()->getUrl();
        $dto->cover = '/files/' . $logotype->getCover()->getBucket() . '/' . $logotype->getCover()->getUrl();
        $dto->id = $logotype->getId();
        $dto->tags = $logotype->getTags();
        $dto->name = $logotype->getName();

        return $dto;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCover(): string
    {
        return $this->cover;
    }
}
