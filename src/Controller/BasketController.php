<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Entity\User;
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
            $totalQuantity = $basket->getTotalQuantity();
            $totalPrice = $basket->getTotalPrice();
            $totalPriceTtc = $basket->getTotalPriceTtc();
            $totalCount = $basket->getItemCount();

            return $this->render('basket/basket.html.twig', [
                'basketItems' => $basketItems,
                'basketIsEmpty' => $basketIsEmpty,
                'totalQuantity' => $totalQuantity,
                'totalPrice' => $totalPrice,
                'totalPriceTtc' => $totalPriceTtc,
                'totalCount' => $totalCount,
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
     */
    #[Route('/mon-panier/{id}/add', name: 'add_item', methods: ['POST'])]
    public function addBasketItem(
        int                    $id,
        Request                $request,
        ProductRepository      $productRepository,
        BasketRepository       $basketRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur non connecté ou non valide'], 400);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['message' => 'Produit non trouvé'], 404);
        }

        $basket = $basketRepository->findOneBy(['user' => $user]);

        if (!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
            $entityManager->persist($basket);
        }

        $quantity = $request->request->get('quantity', 1);
        $item = null;

        if ($basket->hasProduct($product)) {
            $item = $basket->getItemForProduct($product);
            $item->setQuantity($item->getQuantity() + $quantity);
        } else {
            $item = new BasketItem();
            $item->setProduct($product);
            $item->setQuantity($quantity);
            $item->setPrice($product->getPrice());
            $item->setPriceTva($product->getPriceTva());
            $basket->addItem($item);
            $entityManager->persist($item);
        }

        $entityManager->flush();
        $totalItems = $user->countItemsInBasket();

        return new JsonResponse([
            'message' => 'Produit ajouté au panier avec succès',
            'cartItemsCount' => $totalItems
        ]);
    }

    /**
     * This method updates the quantity of a specific item in the user's basket.
     *
     * @param Request $request
     * @param int $id
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse The response containing the status of the update and any relevant messages or data
     * @throws \JsonException
     */
    #[Route('/mon-panier/update/{id}', name: 'update_item', methods: ['PUT'])]
    public function updateQuantity(
        Request                $request,
        int                    $id,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $content = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $newQuantity = (int)($content['quantity'] ?? 1);
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $basket = $user->getBasket();

        if (!($basket instanceof Basket)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid basket type'], 400);
        }

        $items = $basket->getItems();

        foreach ($items as $item) {
            if ($item->getId() === $id) {
                if (!($item instanceof BasketItem)) {
                    return new JsonResponse(['success' => false, 'message' => 'Invalid item type in basket'], 400);
                }

                $oldQuantity = $item->getQuantity();
                $item->setQuantity($newQuantity);

                $entityManager->persist($item);
                $entityManager->flush();

                $itemQuantityChange = $newQuantity - $oldQuantity;

                // Retrieve price and Tva price
                $itemPrice = $item->getPrice();
                $itemTva = $item->getPriceTva();

                return new JsonResponse([
                    'success' => true,
                    'itemQuantityChange' => $itemQuantityChange,
                    'newQuantity' => $newQuantity,
                    'itemPrice' => $itemPrice,
                    'itemTva' => $itemTva,
                    'message' => 'Quantity updated successfully'
                ]);
            }
        }

        return new JsonResponse(['success' => false, 'message' => 'Item not found in basket'], 404);
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
