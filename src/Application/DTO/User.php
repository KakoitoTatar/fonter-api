<?php

declare (strict_types=1);

namespace App\Application\DTO;

/**
 * @OA\Schema
 */
class User
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
    protected string $email;

    /**
     * @OA\Property
     * @var string
     */
    protected string $role;

    /**
     * User constructor.
     */
    private function __construct()
    {
        /**
         * Disable construct
         */
    }

    /**
     * @param \App\Domain\User\User $user
     * @return static
     */
    public static function transform(\App\Domain\User\User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->role = $user->getRole();

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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }
}
