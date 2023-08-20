<?php

declare (strict_types=1);

namespace App\Application\DTO;

/**
 * @OA\Schema
 */
class Font
{
    /**
     * @OA\Property
     * @var int
     */
    protected int $id;

    /**
     * @OA\Property
     * @var string
     */
    protected string $name;

    /**
     * @OA\Property(@OA\Items)
     * @var array
     */
    protected array $tags;

    /**
     * @OA\Property
     * @var string
     */
    protected string $file;

    /**
     * Font constructor.
     */
    private function __construct()
    {
        /**
         * Disable DTO's constructor
         */
    }

    /**
     * @param \App\Domain\Font\Font $font
     * @return static
     */
    public static function transform(\App\Domain\Font\Font $font): self
    {
        $dto = new self();
        $dto->id = $font->getId();
        $dto->tags = $font->getTags();
        $dto->name = $font->getName();
        $dto->file = '/files/' . $font->getFile()->getBucket() . '/' . $font->getFile()->getUrl();

        return $dto;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
