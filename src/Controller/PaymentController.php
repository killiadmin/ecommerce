<?php

namespace App\Controller;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    #[Route('/paiement', name: 'app_payment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function displayPayment(Request $request, PaymentService $paymentService): Response
    {
        $paymentData = $paymentService->getPaymentData($request);

        if ($paymentData['httpRequest']) {
            return $this->json($paymentData['dataBasket']);
        }

        return $this->render('payment/payment.html.twig', [
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
    #[Route('/paiement/ajout', name: 'app_new_payment')]
    public function paiementAjout(Request $request, PaymentService $paymentService): Response
    {
        $result = $paymentService->handlePaymentAddition($request);

        if ($result === true) {
            return $this->redirectToRoute('app_payment');
        }

        return $this->render('payment/newPayment.html.twig', [
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

        return $this->redirectToRoute('app_payment');
    }
}
