<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\PaymentType;
use App\Service\BasketService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    private Security $security;
    private BasketService $basketService;
    private EntityManagerInterface $entityManager;
    private PaymentService $paymentService;

    public function __construct(
        Security $security,
        BasketService $basketService,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService
    ){
        $this->security = $security;
        $this->basketService = $basketService;
        $this->entityManager = $entityManager;
        $this->paymentService = $paymentService;
    }

    #[Route('/paiement', name: 'app_payment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function displayPayment(Request $request): Response
    {
        $user = $this->getUser();
        $basket = $this->getUserBasket();
        $basketItems = $basket->getItems();
        $httpRequest = $request->isXmlHttpRequest();
        $dataBasket = $this->basketService->getBasketData($basket, $httpRequest);

        if ($httpRequest) {
            return $this->json($dataBasket);
        }

        $listPayments = [];
        $paymentSelected = null;

        if ($user instanceof User) {
            $listPayments = $user->getPayments();
            $paymentSelected = $user->getPaymentSelected();
        }

        return $this->render('payment/payment.html.twig', [
            'listPayments' => $listPayments,
            'paymentSelected' => $paymentSelected,
            'dataBasket' => $dataBasket,
            'basketItems' => $basketItems
        ]);
    }

    #[Route('/paiement/ajout', name: 'app_new_payment')]
    public function paiementAjout(Request $request): Response
    {
        $newPayment = new Payment();
        $paymentForm = $this->createForm(PaymentType::class, $newPayment);
        $paymentForm->handleRequest($request);

        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $user = $this->getUser();
            $newPayment->setUserPayment($user);

            $numberPayment = $paymentForm->get('number_payment')->getData();
            $selectPayment = $paymentForm->get('select_payment')->getData();

            $maskedNumberPayment = $this->paymentService->maskCardNumber($numberPayment);
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

            return $this->redirectToRoute('app_payment');
        }

        return $this->render('payment/newPayment.html.twig', [
            'paymentForm' => $paymentForm->createView(),
        ]);
    }

    /**
     * @return RedirectResponse|Basket
     */
    private function getUserBasket(): RedirectResponse | Basket
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $user->getBasket();
    }
}
