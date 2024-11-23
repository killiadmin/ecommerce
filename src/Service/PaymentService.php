<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\User;
use App\Form\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;


class PaymentService
{
    private BasketService $basketService;
    private Security $security;
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(
        BasketService          $basketService,
        Security               $security,
        FormFactoryInterface   $formFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->basketService = $basketService;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;

    }

    /**
     * @param Request $request
     * @return array
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
            $newPayment->setMaskedNumberPayment($maskedNumberPayment);

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
     * @param string $cardNumber
     * @return string
     */
    public function maskCardNumber(string $cardNumber): string
    {
        $masked = str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
        return implode(' ', str_split($masked, 4));
    }
}
