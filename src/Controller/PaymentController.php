<?php

namespace App\Controller;

use App\Repository\UserAddressRepository;
use App\Service\PaymentService;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
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
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException|\JsonException
     */
    #[Route('/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $datas = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $amount = $datas['amount'] ?? 9999;
        $description = $datas['description'] ?? 'Opération de test';

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'description' => $description,
                'metadata' => [
                    'code_order' => '12345',
                    'cardholder_name' => 'Anonyme',
                ],
            ]);

            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
