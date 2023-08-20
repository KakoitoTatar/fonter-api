<?php

declare (strict_types=1);

namespace App\Domain\Logotype;

use App\Domain\Media\Media;
use App\Domain\User\User;

interface LogotypeRepositoryInterface
{
    /**
     * @param int $id
     * @return Logotype
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Logotype
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Logotype
     */
    public function findOneBy(array $conditions);

    /**
     * @param string $name
     * @param Media $file
     * @param Media $cover
     * @param User $author
     * @param array $tags
     * @return Logotype
     */
    public function create(string $name, Media $file, Media $cover, User $author, array $tags): Logotype;

    /**
     * @param int $id
     * @return Logotype
     */
    public function read(int $id): Logotype;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param int $id
     * @param string|null $name
     * @param Media|null $file
     * @param Media|null $cover
     * @param array|null $tags
     * @return Logotype
     */
    public function update(int $id, ?string $name, ?Media $file, ?Media $cover, ?array $tags): Logotype;

    /**
     * @param int $size
     * @param int $offset
     * @param string|null $name
     * @param array|null $tags
     * @return mixed
     */
    public function readByPages(int $size = 20, int $offset = 0, ?string $name = null, ?array $tags = null);

    /**
     * @param string|null $name
     * @param array|null $tags
     * @return mixed
     */
    public function total(?string $name = null, ?array $tags = null): int;
}
