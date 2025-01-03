<?php

namespace App\Service;

use App\Entity\Basket;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\BasketItem;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BasketService
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security,)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     *  Retrieves data related to the basket, including items, quantities, prices, and discounts.
     *
     * @param Basket $basket
     * @param bool $httpRequest
     * @return array
     * @throws CircularReferenceException
     * @throws LogicException
     * @throws InvalidArgumentException|ExceptionInterface
     */
    public function getBasketData(Basket $basket, bool $httpRequest): array
    {
        $user = $this->security->getUser();
        $basketItems = $basket->getItems();

        $userData = [
            'isProfessional' => $user->isProfessional(),
        ];

        $data = [
            'user' => $userData,
            'basketItems' => $basketItems,
            'basketIsEmpty' => $basketItems->isEmpty(),
            'totalQuantity' => $basket->getTotalQuantity(),
            'totalPrice' => $basket->getTotalPrice(),
            'totalPriceTtc' => $basket->getTotalPriceTtc(),
            'totalCount' => $basket->getItemCount(),
            'totalPriceWithDiscount' => $basket->getTotalPriceWithDiscount(),
            'totalPriceTtcWithDiscount' => $basket->getTotalPriceTtcWithDiscount(),
            'valueDiscount' => $basket->getAppliedDiscountAmount(),
        ];

        if ($httpRequest) {
            $normalizer = new ObjectNormalizer();
            $serializer = new Serializer([$normalizer]);

            $normalizedBasketItems = $serializer->normalize(
                $basketItems, null, [AbstractNormalizer::ATTRIBUTES => [
                    'id', 'quantity', 'price', 'priceTva'
                ]]
            );

            $data['basketItems'] = $normalizedBasketItems;
        }

        return $data;
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
