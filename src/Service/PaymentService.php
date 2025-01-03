<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class PaymentService
{
    private BasketService $basketService;
    private Security $security;
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $entityManager;
    private UserService $userService;

    public function __construct(
        BasketService          $basketService,
        Security               $security,
        FormFactoryInterface   $formFactory,
        EntityManagerInterface $entityManager,
        UserService            $userService,
    )
    {
        $this->basketService = $basketService;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->userService = $userService;

    }

    /**
     * @param Request $request
     * @return array
     * @throws ExceptionInterface
     */
    public function getPaymentData(Request $request): array
    {
        $user = $this->security->getUser();
        $basket = $user->getBasket();
        $basketItems = $basket->getItems();
        $httpRequest = $request->isXmlHttpRequest();
        $dataBasket = $this->basketService->getBasketData($basket, $httpRequest);

        $listPayments = [];
        $paymentSelected = null;

        if ($user instanceof User) {
            $listPayments = $user->getPayments();
            $paymentSelected = $user->getPaymentSelected();
        }

        return [
            'httpRequest' => $httpRequest,
            'listPayments' => $listPayments,
            'paymentSelected' => $paymentSelected,
            'dataBasket' => $dataBasket,
            'basketItems' => $basketItems,
        ];
    }

    /**
     * @param Request $request
     * @return true|FormInterface
     */
    public function handlePaymentAddition(Request $request): true|FormInterface
    {
        $newPayment = new Payment();
        $paymentForm = $this->formFactory->create(PaymentType::class, $newPayment);
        $paymentForm->handleRequest($request);

        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $user = $this->security->getUser();
            $newPayment->setUserPayment($user);

            $numberPayment = $paymentForm->get('number_payment')->getData();
            $selectPayment = $paymentForm->get('select_payment')->getData();

            $maskedNumberPayment = $this->maskCardNumber($numberPayment);
            $hashedNumberPayment = $this->hashCardNumber($numberPayment);

            $newPayment->setMaskedNumberPayment($maskedNumberPayment);
            $newPayment->setNumberPayment($hashedNumberPayment);

            $newPayment->setActivePayment(1);
            $newPayment->setSelectPayment($selectPayment ?? false);
            $newPayment->setCreatedAt(new \DateTimeImmutable());

            if ($selectPayment) {
                $existingSelectPayment = $this->entityManager->getRepository(Payment::class)->findOneBy([
                    'user_payment' => $user,
                    'select_payment' => true
                ]);

                if ($existingSelectPayment) {
                    $existingSelectPayment->setSelectPayment(false);
                    $this->entityManager->persist($existingSelectPayment);
                }
            }

            $this->entityManager->persist($newPayment);
            $this->entityManager->flush();

            return true;
        }

        return $paymentForm;
    }

    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function selectPayment(int $id): void
    {
        $user = $this->security->getUser();

        $payment = $this->entityManager->getRepository(Payment::class)->findOneBy([
            'id' => $id,
            'user_payment' => $user
        ]);

        if (!$payment) {
            throw new \RuntimeException('Paiement non trouvé.');
        }

        $this->entityManager->getRepository(Payment::class)->deselectAllPaymentsForUser($user);

        $payment->setSelectPayment(true);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    /**
     * @param $user
     * @return JsonResponse|RedirectResponse
     * @throws ExceptionInterface
     */
    public function createPaymentIntent($user): JsonResponse|RedirectResponse
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

        if (!$user) {
            return new JsonResponse(['error' => "Utilisateur non authentifié"], 401);
        }

        $order = new Order();

        try {
            $order->setUserOrder($user);
            $order->setCodeOrder(uniqid('ORDER_', true));
            $order->setTotalPriceOrder($amount / 100);
            $order->setTotalQuantityOrder($quantity);
            $order->setPaymentOrder('stripe');
            $order->setValidateOrder(false);
            $order->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($order);

            foreach ($basket->getItems() as $basketItem) {
                $product = $basketItem->getProduct();

                if (!$product) {
                    continue;
                }

                $orderDetail = new OrderDetails();
                $orderDetail->setOrderAssociated($order);
                $orderDetail->setProductAssociated($product);
                $orderDetail->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($orderDetail);
                $order->addOrderDetail($orderDetail);
            }

            $this->entityManager->flush();
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
                'orderCode' => $order->getCodeOrder(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param string $cardNumber
     * @return string
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $masked = str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
        return implode(' ', str_split($masked, 4));
    }

    /**
     * @param string $cardNumber
     * @return string
     */
    private function hashCardNumber(string $cardNumber): string
    {
        return hash('sha256', $cardNumber);
    }
}
