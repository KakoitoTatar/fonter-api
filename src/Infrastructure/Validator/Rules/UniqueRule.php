<?php

namespace App\Infrastructure\Validator\Rules;

use App\Application\Validator\Rules\UniqueRuleInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueRule implements UniqueRuleInterface
{
    /**
     * @var string
     */
    protected string $message = "{field} must be unique";

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
        return 'unique';
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
        $entityField = 'e.' . $params[1];

        $qb = $this->em->createQueryBuilder();
        $qb->select('count(\'' . $entity . '\') as count');
        $qb->from($entity, 'e');
        $qb->andWhere($entityField . '=\'' . $value . '\'');
        $data = $qb->getQuery()->getSingleResult();

        return (int)$data['count'] === 0;
    }
}
