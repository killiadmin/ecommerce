<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\UserAddressRepository;
use App\Service\BasketService;
use App\Service\PaymentService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PaymentController extends AbstractController
{
    private BasketService $basketService;
    private UserService $userService;

    public function __construct(BasketService $basketService, UserService $userService)
    {
        $this->basketService = $basketService;
        $this->userService = $userService;
    }

    /**
     * @param Request $request
     * @param PaymentService $paymentService
     * @return Response
     */
    #[Route('/paiement', name: 'app_payment_custom')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function displayCustomPayment(Request $request, PaymentService $paymentService): Response
    {
        $paymentData = $paymentService->getPaymentData($request);

        if ($paymentData['httpRequest']) {
            return $this->json($paymentData['dataBasket']);
        }

        return $this->render('payment/custom/payment.html.twig', [
            'listPayments' => $paymentData['listPayments'],
            'paymentSelected' => $paymentData['paymentSelected'],
            'dataBasket' => $paymentData['dataBasket'],
            'basketItems' => $paymentData['basketItems']
        ]);
    }

    /**
     * @param Request $request
     * @param PaymentService $paymentService
     * @return Response
     */
    #[Route('/paiement-stripe', name: 'app_payment_stripe')]
    public function displayStripePayment(Request $request, PaymentService $paymentService): Response
    {
        $paymentData = $paymentService->getPaymentData($request);

        if ($paymentData['httpRequest']) {
            return $this->json($paymentData['dataBasket']);
        }

        return $this->render('payment/stripe/payment-stripe.html.twig', [
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'dataBasket' => $paymentData['dataBasket'],
            'basketItems' => $paymentData['basketItems'],
        ]);
    }

    /**
     * @param Request $request
     * @param PaymentService $paymentService
     * @return Response
     */
    #[Route('/paiement/ajout', name: 'app_new_payment')]
    public function paiementAjout(Request $request, PaymentService $paymentService): Response
    {
        $result = $paymentService->handlePaymentAddition($request);

        if ($result === true) {
            return $this->redirectToRoute('app_payment_custom');
        }

        return $this->render('payment/custom/newPayment.html.twig', [
            'paymentForm' => $result->createView(),
        ]);
    }

    /**
     * @param int $id
     * @param PaymentService $paymentService
     * @return Response
     */
    #[Route('/paiement/select/{id}', name: 'app_select_payment')]
    public function paiementSelect(int $id, PaymentService $paymentService): Response
    {
        try {
            $paymentService->selectPayment($id);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->redirectToRoute('app_payment_custom');
    }

    #[Route('/payment/infos', name: 'app_payment_infos', methods: ['GET'])]
    public function getInfosPayment(
        UserAddressRepository $userAddressRepository,
        Security              $security
    ): JsonResponse
    {
        $user = $security->getUser();
        $email = null;
        $phone = null;
        $addressArray = null;

        if ($user) {
            $email = $user->getEmail();
            $phone = "+33 629087261";
        }

        $address = $userAddressRepository->findOneBy(['user_associated' => $user]);

        if ($address) {
            $addressArray = [
                'line1' => $address->getNumberBilling() . ' ' . $address->getLibelleBilling(),
                'city' => $address->getCityBilling(),
                'postal_code' => $address->getCodeBilling(),
            ];
        }

        return new JsonResponse([
            'email' => $email,
            'phone' => $phone,
            'address' => $addressArray,
        ]);
    }

    /**
     * Handles the creation of a payment intent using Stripe API and saves the associated order in the database.
     *
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(EntityManagerInterface $entityManager): RedirectResponse|JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $basket = $this->userService->getUserBasket();

        if ($basket instanceof RedirectResponse) {
            return $basket;
        }

        if ($basket->getItems()->isEmpty()) {
            return new JsonResponse(['error' => 'Panier vide ou introuvable'], 400);
        }

        $basketData = $this->basketService->getBasketData($basket, false);

        $amount = $basketData['totalPriceTtcWithDiscount'] !== $basketData['totalPriceTtc']
            ? $basketData['totalPriceTtcWithDiscount'] * 100
            : $basketData['totalPriceTtc'] * 100;

        $quantity = $basketData['totalQuantity'];
        $description = $quantity . ' articles vendus';

        $getUser = $this->getUser();

        if (!$getUser) {
            return new JsonResponse(['error' => "Utilisateur non authentifié"], 401);
        }

        $order = new Order();

        try {
            $order->setUserOrder($getUser);
            $order->setCodeOrder(uniqid('ORDER_', true));
            $order->setTotalPriceOrder($amount / 100);
            $order->setTotalQuantityOrder($quantity);
            $order->setPaymentOrder('stripe');
            $order->setValidateOrder(false);
            $order->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($order);

            foreach ($basket->getItems() as $basketItem) {
                $product = $basketItem->getProduct();

                if (!$product) {
                    continue;
                }

                $orderDetail = new OrderDetails();
                $orderDetail->setOrderAssociated($order);
                $orderDetail->setProductAssociated($product);
                $orderDetail->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($orderDetail);
                $order->addOrderDetail($orderDetail);
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la création de la commande : ' . $e->getMessage()], 500);
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'description' => $description,
                'metadata' => [
                    'code_order' => $order->getCodeOrder(),
                    'description' => $description,
                ],
                'capture_method' => 'automatic',
            ]);

            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
