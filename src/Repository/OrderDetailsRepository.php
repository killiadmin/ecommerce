<?php

namespace App\Repository;

use App\Entity\OrderDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderDetails>
 */
class OrderDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDetails::class);
    }

    public function findProductsByOrderId(int $orderId): array
    {
        return $this->createQueryBuilder('od')
            ->join('od.product_associated', 'p')
            ->addSelect('p') // Sélectionne également les produits associés
            ->andWhere('od.order_associated = :order')
            ->setParameter('order', $orderId)
            ->getQuery()
            ->getResult();
    }
}
