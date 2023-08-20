<?php
declare(strict_types=1);

namespace App\Domain\Media;

interface MediaRepositoryInterface
{
    /**
     * @param int $id
     * @return Media
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Media
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Media
     */
    public function findOneBy(array $conditions);

    /**
     * @param string $url
     * @return Media
     */
    public function save(string $url): Media;

    /**
     * @param int $id
     * @param string|null $url
     * @param bool|null $temporal
     * @return Media
     */
    public function update(int $id, ?string $url, ?bool $temporal = null): Media;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
