<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handleStripeWebhook(EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endPointSecret = $_ENV['STRIPE_ENDPOINT_SECRET'];

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endPointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return new JsonResponse(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $codeOrder = $paymentIntent->metadata->code_order;

                $order = $entityManager->getRepository(Order::class)->findOneBy(['code_order' => $codeOrder]);

                if ($order) {
                    $order->setValidateOrder(true);
                    $entityManager->flush();

                    error_log('Commande validée pour PaymentIntent : ' . $paymentIntent->id);
                } else {
                    error_log('Commande introuvable pour le code_order : ' . $codeOrder);
                }
                break;

            default:
                error_log('Événement ignoré : ' . $event->type);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
