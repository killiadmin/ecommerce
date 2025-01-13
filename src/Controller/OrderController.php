<?php

namespace App\Controller;

use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @param OrderDetailsRepository $orderDetailsRepository
     * @return Response
     */
    #[Route('/order', name: 'app_order')]
    public function viewLastOrder(OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Retrieve last order
        $lastOrder = $orderRepository->findLastOrderForUser($user);

        if (!$lastOrder) {
            return $this->render('order/order.html.twig', [
                'lastOrder' => null,
                'products' => [],
            ]);
        }

        // Retrieve the products associated with the order
        $products = $orderDetailsRepository->findProductsByOrderId($lastOrder->getId());

        return $this->render('order/order.html.twig', [
            'lastOrder' => $lastOrder,
            'products' => $products,
        ]);
    }
}
