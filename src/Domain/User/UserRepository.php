<?php
declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Media\Media;

interface UserRepository
{
    /**
     * @param int $id
     * @return User
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return User
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return User
     */
    public function findOneBy(array $conditions);

    /**
     * @param int $id
     * @return User|null
     */
    public function getInactiveUser(int $id): ?User;

    /**
     * @param User $user
     * @return mixed
     */
    public function save(User $user);

    /**
     * @return User[]
     */
    public function findAll(): array;
}
