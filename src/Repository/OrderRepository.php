<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param User $user
     * @return Order|null
     */
    public function findLastOrderForUser(User $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user_order = :user')
            ->setParameter('user', $user)
            ->orderBy('o.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findAllOrdersForUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user_order = :user')
            ->setParameter('user', $user)
            ->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
