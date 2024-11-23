<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Deselects all payment methods for a given user
     *
     * @param mixed $user
     * @return mixed
     */
    public function deselectAllPaymentsForUser(mixed $user): mixed
    {
        return $this->createQueryBuilder('p')
            ->update()
            ->set('p.select_payment', ':false')
            ->where('p.user_payment = :user')
            ->setParameter('false', false)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
