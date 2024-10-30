<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\BasketItem;
use Symfony\Component\HttpFoundation\JsonResponse;

class BasketService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Removes an item from the basket and updates the basket status accordingly.
     *
     * @param BasketItem $item
     * @param mixed $basket
     * @return JsonResponse
     */
    public function removeBasketItem(BasketItem $item, mixed $basket): JsonResponse
    {
        $this->em->remove($item);
        $this->em->flush();

        if ($basket->getItemCount() === 0) {
            $basket->setDiscountCode(null);
            $this->em->persist($basket);
            $this->em->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Cancels the discount code associated with the given basket.
     *
     * @param mixed $basket
     * @return void
     */
    public function cancelDiscountCode(mixed $basket): void
    {
        $basket->setDiscountCode(null);
        $this->em->persist($basket);
        $this->em->flush();
    }
}
