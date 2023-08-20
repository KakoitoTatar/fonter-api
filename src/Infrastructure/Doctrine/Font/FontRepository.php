<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Font;

use App\Application\Helpers\StringHelper;
use App\Application\Helpers\TagsHelper;
use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\Font\Font;
use App\Domain\Font\FontRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\User\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Font find($id, $lockMode = null, $lockVersion = null)
 */
class FontRepository extends EntityRepository implements FontRepositoryInterface
{
    /**
     * @param string $name
     * @param array $tags
     * @param User $author
     * @param Media $file
     * @return Font
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $name, array $tags, User $author, Media $file): Font
    {
        $logotype = new Font();
        $logotype->setName($name)
            ->setAuthor($author)
            ->setFile($file)
            ->setTags(TagsHelper::prepareTags($tags));

        $this->getEntityManager()->persist($logotype);
        $this->getEntityManager()->flush();

        return $logotype;
    }

    /**
     * @param int $id
     * @return Font
     * @throws DomainRecordNotFoundException
     */
    public function read(int $id): Font
    {
        $font =  $this->find($id);

        if ($font === null) {
            throw new DomainRecordNotFoundException(
                'Font with id:' . $id . ' not found', 404
            );
        }

        return $font;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $this->getEntityManager()->remove($this->find($id));
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int $id
     * @param string|null $name
     * @param array|null $tags
     * @param Media|null $file
     * @return Font
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(int $id, ?string $name, ?array $tags, ?Media $file): Font
    {
        $font = $this->find($id);

        if ($font === null) {
            throw new DomainRecordNotFoundException('Font with id:' . $id . ' not found');
        }

        if ($file !== null) {
            $font->getFile()->setTemporal(true);
            $font->setFile($file);
        }

        $font->setName($name ?? $font->getName());

        if(($tags !== null) || $tags === []) {
            $font->setTags(TagsHelper::prepareTags($tags));
        }

        $this->getEntityManager()->flush();

        return $font;
    }

    /**
     * @param int $size
     * @param int $offset
     * @return array
     */
    public function readByPages(int $size = 20, int $offset = 0, ?string $name = null, ?array $tags = null): array
    {
        $query = $this->createQueryBuilder('f')
            ->setFirstResult($offset)
            ->setMaxResults($size);

        if ($name) {
            $query->where('l.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($tags) {
            foreach ($tags as $key => $tag) {
                $query->andWhere($query->expr()->like('l.tags', ':tag' . $key))
                    ->setParameter(':tag' . $key, '%' . $tag . '%');
            }
        }

        $paginator = new Paginator($query);

        return $paginator->getQuery()->getResult();
    }

    /**
     * @param string|null $name
     * @param array|null $tags
     * @return mixed|void
     */
    public function total(?string $name = null, ?array $tags = null): int
    {
        $query = $this->createQueryBuilder('f');
        $query->select('count(f.id)');

        if ($name) {
            $query->where('f.name LIKE :name')
                ->setParameter('name', $name);
        }

        if ($tags) {
            foreach ($tags as $key => $tag) {
                $query->andWhere($query->expr()->like('f.tags', ':tag' . $key))
                    ->setParameter(':tag' . $key, '%' . $tag . '%');
            }
        }

        return $query->getQuery()->getResult()[0][1];
    }
}
