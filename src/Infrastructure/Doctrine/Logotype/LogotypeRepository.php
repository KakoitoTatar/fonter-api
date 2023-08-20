<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Logotype;

use App\Application\Helpers\StringHelper;
use App\Application\Helpers\TagsHelper;
use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\Logotype\Logotype;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\User\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogotypeRepository extends EntityRepository implements LogotypeRepositoryInterface
{
    /**
     * @param string $name
     * @param Media $file
     * @param Media $cover
     * @param User $author
     * @param array $tags
     * @return Logotype
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(string $name, Media $file, Media $cover, User $author, array $tags): Logotype
    {
        $logotype = new Logotype();
        $logotype->setName($name);
        $logotype->setFile($file);
        $logotype->setAuthor($author);
        $logotype->setCover($cover);
        $logotype->setTags(TagsHelper::prepareTags($tags));
        $logotype->setCreatedAt(new \DateTime());

        $this->getEntityManager()->persist($logotype);
        $this->getEntityManager()->flush();

        return $logotype;
    }

    /**
     * @param int $id
     * @return Logotype
     * @throws DomainRecordNotFoundException
     */
    public function read(int $id): Logotype
    {
        /** @var Logotype $logotype */
        $logotype = $this->find($id);

        if ($logotype === null) {
            throw new DomainRecordNotFoundException('Logotype with id:' . $id . ' not found', 404);
        }

        return $logotype;
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
     * @param Media|null $file
     * @param Media|null $cover
     * @param User|null $author
     * @param array|null $tags
     * @return Logotype
     * @throws DomainRecordNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(int $id, ?string $name, ?Media $file, ?Media $cover, ?array $tags): Logotype
    {
        /** @var Logotype $logotype */
        $logotype = $this->find($id);

        if ($logotype === null) {
            throw new DomainRecordNotFoundException('Logotype with id:' . $id . ' not found', 404);
        }

        $logotype->setName($name ?? $logotype->getName());
        $logotype->setFile($file ?? $logotype->getFile());
        $logotype->setCover($cover ?? $logotype->getCover());
        $logotype->setTags(TagsHelper::prepareTags($tags));

        $this->getEntityManager()->flush();

        return $logotype;
    }

    /**
     * @param int $size
     * @param int $offset
     * @param string|null $name
     * @param array|null $tags
     * @return array
     */
    public function readByPages(
        int     $size = 20,
        int     $offset = 0,
        ?string $name = null,
        ?array  $tags = null
    ): array
    {
        $query = $this->createQueryBuilder('l')
            ->setFirstResult($offset)
            ->setMaxResults($size);

        if ($name) {
            $query->andWhere('l.name like :name')
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
        $query = $this->createQueryBuilder('l');
        $query->select('count(l.id)');

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

        return $query->getQuery()->getResult()[0][1];
    }
}
