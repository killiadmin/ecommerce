<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function displayPayment(): Response
    {
        return $this->render('payment/payment.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
