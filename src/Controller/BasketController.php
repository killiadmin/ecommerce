<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function listBasket(Request $request): Response
    {
        // Get the current user
        $user = $this->security->getUser();

        // If user is not logged in, redirect to login page
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $request->getSession()->set('last_page', $request->getUri());

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

    /**
     * This method adds a product to the user's basket.
     *
     * @param int $id The ID of the product to add.
     * @param ProductRepository $productRepository The repository for product entities.
     * @param BasketRepository $basketRepository The repository for basket entities.
     * @param EntityManagerInterface $entityManager The entity manager for persisting changes.
     * @return RedirectResponse The redirect response to the product page.
     */
    #[Route('/mon-panier/{id}/add', name: 'add_item')]
    public function addBasketItem(
        int                    $id,
        ProductRepository      $productRepository,
        BasketRepository       $basketRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $user = $this->getUser();
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
            $entityManager->persist($basket);
        }

        //Initialisation de $item
        $item = null;

        if ($basket->hasProduct($product)) {
            $item = $basket->getItemForProduct($product);
            $item->setQuantity($item->getQuantity() + 1);
        } else {
            $item = new BasketItem();
            $item->setProduct($product);
            $item->setQuantity(1);
            $item->setPrice($product->getPrice());
            $basket->addItem($item);
            $entityManager->persist($item);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_product');
    }

    /**
     * This method removes an item from the user's basket.
     *
     * @param EntityManagerInterface $em The entity manager to manage the removal of the item
     * @param BasketItem $item The item to be removed from the basket
     *
     * @return JsonResponse The response that redirects to the user's basket after removing the item
     */
    #[Route('/mon-panier/{id}/delete', name: 'delete_item')]
    public function removeBasketItem(EntityManagerInterface $em, BasketItem $item): JsonResponse
    {
        $em->remove($item);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
