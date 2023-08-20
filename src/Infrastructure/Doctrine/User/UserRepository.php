<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\User;

use App\Domain\DomainException\UniqueConstraintViolationException;
use App\Domain\User\User;
use Doctrine\ORM\EntityRepository;
use App\Domain\User\UserNotFoundException;
use Doctrine\ORM\NoResultException;

class UserRepository extends EntityRepository implements \App\Domain\User\UserRepository
{
    /**
     * @param string $email
     * @param string $password
     * @return User
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActiveUserByEmail(string $email): ?User
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.role!=:role');
        $qb->andWhere('u.email=:email');
        $qb->setParameter('email', $email);
        $qb->setParameter('role', User::INACTIVE_USER);
        try {
            $user = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
        return $user;
    }

    /**
     * @param int $id
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getInactiveUser(int $id): ?User
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.role = :role');
        $qb->andWhere('u.id = :id');
        $qb->setParameter('email', $id);
        $qb->setParameter('role', User::INACTIVE_USER);
        try {
            $user = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new UserNotFoundException('User with that id not found');
        }

        return $user;
    }

    /**
     * @param User $user
     * @return User
     * @throws UniqueConstraintViolationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        try {
            $this->getEntityManager()->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new UniqueConstraintViolationException(
                'Пользователь с данным email уже зарегистрирован',
                400
            );
        }

        return $user;
    }

    public function findAll(): array
    {
        $this->createQueryBuilder('u')->getQuery()->getResult();
    }
}