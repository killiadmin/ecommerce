<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use App\Service\BasketService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    private BasketService $basketService;
    private UserService $userService;

    public function __construct(BasketService $basketService, UserService $userService)
    {
        $this->basketService = $basketService;
        $this->userService = $userService;
    }

    #[Route('/order', name: 'app_order')]
    public function viewOrder(): Response
    {
        return $this->render('order/order.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/order/create', name: 'app_order_create', methods: ['POST'])]
    public function createOrderFromBasket(Request $request, EntityManagerInterface $em): RedirectResponse|JsonResponse
    {
        $basket = $this->userService->getUserBasket();

        if ($basket instanceof RedirectResponse) {
            return $basket;
        }

        if ($basket->getItems()->isEmpty()) {
            return new JsonResponse(['error' => 'Panier vide ou introuvable'], 400);
        }

        $data = $this->basketService->getBasketData($basket, false);

        dd($data);


        /*
        $order = new Order();
        $order->setUserOrder($this->getUser());
        $order->setCodeOrder(uniqid('ORDER_', true));
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setValidateOrder(false);

        $totalPrice = 0;
        $totalQuantity = 0;

        foreach ($basket->getItems() as $basketItem) {
            $orderDetails = new OrderDetails();
            $orderDetails->setOrderAssociated($order);
            $orderDetails->setProductAssociated($basketItem->getProduct());
            $orderDetails->setCreatedAt(new \DateTimeImmutable());
            $orderDetails->setQuantity($basketItem->getQuantity());

            $totalQuantity += $basketItem->getQuantity();
            $totalPrice += $basketItem->getProduct()->getPrice() * $basketItem->getQuantity();

            $em->persist($orderDetails);
        }

        $order->setTotalQuantityOrder($totalQuantity);
        $order->setTotalPriceOrder($totalPrice);
        $em->persist($order);

        $em->flush();

        return new JsonResponse(['message' => 'Commande créée avec succès', 'order_id' => $order->getId()], 201);*/
    }
}
