<?php

declare (strict_types=1);

namespace App\Infrastructure\Validator\Rules;

use App\Application\Validator\Rules\EntityExistsRuleInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class EntityExistsRule implements EntityExistsRuleInterface
{
    /**
     * @var string
     */
    protected string $message = '{field} entity doesn\'t exist';

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * UniqueRule constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'exists';
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function check($field, $value, array $params, array $fields): bool
    {
        $entity = $params[0];

        $qb = $this->em->createQueryBuilder();
        $qb->select('count(\'' . $entity . '\') as count');
        $qb->from($entity, 'e');
        $qb->andWhere('id=\'' . $value . '\'');
        $data = $qb->getQuery()->getOneOrNullResult();

        return $data !== null;
    }
}
