<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mail;

use App\Domain\Mail\Mail;
use App\Domain\Mail\MailRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class MailRepository extends EntityRepository implements MailRepositoryInterface
{
    /**
     * @return array
     */
    public function getAllNewNotSendedMails(): array
    {
        $qb = $this->createQueryBuilder('m');

        $qb->andWhere('m.sendAt < :sendAt')
            ->andWhere('m.status = :status')
            ->setParameter('sendAt', new \DateTime())
            ->setParameter('status', Mail::READY);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Mail $mail
     * @return Mail
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Mail $mail): Mail
    {
        $this->getEntityManager()->persist($mail);
        $this->getEntityManager()->flush();

        return $mail;
    }
}
