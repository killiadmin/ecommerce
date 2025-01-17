<?php

namespace App\Controller;

use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Service\BasketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @param OrderDetailsRepository $orderDetailsRepository
     * @param BasketService $basketService
     * @return Response
     */
    #[Route('/order', name: 'app_order')]
    public function viewLastOrder(OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository, BasketService $basketService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Retrieve last order
        $lastOrder = $orderRepository->findLastOrderForUser($user);

        if (!$lastOrder) {
            return $this->render('order/lastOrder.html.twig', [
                'lastOrder' => null,
                'products' => [],
            ]);
        }

        // Retrieve the products associated with the order
        $products = $orderDetailsRepository->findProductsByOrderId($lastOrder->getId());

        $basketService->clearBasketForUser($user);

        return $this->render('order/lastOrder.html.twig', [
            'lastOrder' => $lastOrder,
            'products' => $products,
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/list-orders', name: 'app_list-orders')]
    public function viewListOrders(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $orders = $orderRepository->findAllOrdersForUser($user);

        return $this->render('order/listOrders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @param int $id
     * @param OrderRepository $orderRepository
     * @param OrderDetailsRepository $orderDetailsRepository
     * @return Response
     */
    #[Route('/order/{id}', name: 'app_order_details')]
    public function viewOrderDetails(
        int                    $id,
        OrderRepository        $orderRepository,
        OrderDetailsRepository $orderDetailsRepository
    ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $order = $orderRepository->find($id);

        if (!$order || $order->getUserOrder() !== $user) {
            return $this->redirectToRoute('app_list-orders');
        }

        $products = $orderDetailsRepository->findProductsByOrderId($id);

        return $this->render('order/detailsOrder.html.twig', [
            'order' => $order,
            'products' => $products,
        ]);
    }
}
