<?php

declare(strict_types=1);

namespace App\Domain\Font;

use App\Domain\Media\Media;
use App\Domain\User\User;

interface FontRepositoryInterface
{
    /**
     * @param int $id
     * @return Font
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Font
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Font
     */
    public function findOneBy(array $conditions);

    /**
     * @param string $name
     * @param array $tags
     * @param User $author
     * @param Media $file
     * @return Font
     */
    public function create(string $name, array $tags, User $author, Media $file): Font;

    /**
     * @param int $id
     * @return Font
     */
    public function read(int $id): Font;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param int $id
     * @param string|null $name
     * @param array|null $tags
     * @param Media|null $file
     * @return Font
     */
    public function update(int $id, ?string $name, ?array $tags, ?Media $file): Font;

    /**
     * @param int $size
     * @param int $offset
     * @return mixed
     */
    public function readByPages(int $size = 20, int $offset = 0, ?string $name = null, ?array $tags = null);

    /**
     * @param string|null $name
     * @param array|null $tags
     * @return int
     */
    public function total(?string $name = null, ?array $tags = null): int;
}
