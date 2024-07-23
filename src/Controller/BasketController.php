<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BasketController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * This method displays the user's basket.
     *
     * @return Response The response containing the rendered template with the basket items or empty basket message
     */
    #[Route('/mon-panier', name: 'app_basket')]
    public function listBasket(): Response
    {
        // Get the current user
        $user = $this->security->getUser();

        // If user is not logged in, redirect to login page
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Get the user's basket
        $basket = $user->getBasket();

        // Check if the basket exists and has items
        if ($basket) {
            $basketItems = $basket->getItems();
            $basketIsEmpty = $basketItems->isEmpty();

            return $this->render('basket/basket.html.twig', [
                'basketItems' => $basketItems,
                'basketIsEmpty' => $basketIsEmpty,
            ]);
        }

        // If no basket exists for the user, render the basket template with an empty state
        return $this->render('basket/basket.html.twig', [
            'basketIsEmpty' => true,
        ]);
    }
}
