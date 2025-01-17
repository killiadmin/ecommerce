<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Entity\DiscountCode;
use App\Entity\User;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use App\Service\BasketService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class BasketController extends AbstractController
{
    private Security $security;
    private BasketService $basketService;

    public function __construct(Security $security, BasketService $basketService)
    {
        $this->security = $security;
        $this->basketService = $basketService;
    }

    /**
     * This method displays the user's basket.
     *
     * @return Response The response containing the rendered template with the basket items or empty basket message
     * @throws ExceptionInterface
     */
    #[Route('/mon-panier', name: 'app_basket')]
    public function listBasket(
        Request          $request,
        SessionInterface $session): Response
    {
        $session->remove('basket_validated');
        $session->remove('delivery_validated');
        $session->remove('order_validated');

        $basket = $this->getUserBasket();
        $request->getSession()->set('last_page', $request->getUri());

        if ($basket instanceof Basket) {
            $httpRequest = $request->isXmlHttpRequest();
            $data = $this->basketService->getBasketData($basket, $httpRequest);

            if ($httpRequest) {
                return $this->json($data);
            }

            return $this->render('basket/basket.html.twig', $data);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['basketIsEmpty' => true]);
        }

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
        $content = json_decode($request->getContent(), true);
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
        $itemFound = false;

        foreach ($items as $item) {
            if ($item->getId() === $id) {
                if (!($item instanceof BasketItem)) {
                    return new JsonResponse(['success' => false, 'message' => 'Invalid item type in basket'], 400);
                }

                $item->setQuantity($newQuantity);
                $entityManager->persist($item);
                $itemFound = true;
            }
        }

        if ($itemFound) {
            $entityManager->flush();
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Item not found in basket'], 404);
    }

    /**
     * This method removes an item from the user's basket.
     *
     * @param BasketItem $item The item to be removed from the basket
     * @return RedirectResponse|JsonResponse The response that redirects to the user's basket after removing the item
     */
    #[Route('/mon-panier/{id}/delete', name: 'delete_item')]
    public function removeBasketItem(BasketItem $item, BasketService $basketService): RedirectResponse | JsonResponse
    {
        $basket = $this->getUserBasket();

        if ($basket instanceof RedirectResponse) {
            return $basket;
        }

        return $this->basketService->removeBasketItem($item, $basket);
    }

    /**
     * This method applies a discount code to the user's basket.
     *
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('/mon-panier/code', name: 'basket_discount_code', methods: ['POST'])]
    public function applyDiscountCode(Request $request, ManagerRegistry $doctrine): Response
    {
        $basket = $this->getUserBasket();

        if ($basket instanceof RedirectResponse) {
            return $basket;
        }

        $discountCodeName = $request->request->get('promo_code');

        $discountCode = $doctrine
            ->getRepository(DiscountCode::class)
            ->findOneBy(['name_code' => $discountCodeName]);

        if ($discountCode && $discountCode->isActive()) {
            $basket->setDiscountCode($discountCode);
            $em = $doctrine->getManager();
            $em->persist($basket);
            $em->flush();

            $this->addFlash('success', 'Code de réduction appliqué avec succès');
            return $this->redirectToRoute('app_basket');
        }

        $this->addFlash('error', 'Votre code promotionnel est incorrecte ou inactif');
        return $this->redirectToRoute('app_basket');
    }

    /**
     * This method cancels the discount code applied to the basket.
     *
     * @return Response
     */
    #[Route('/mon-panier/code/cancel', name: 'basket_discount_code_cancel')]
    public function cancelDiscountCode(): Response
    {
        $basket = $this->getUserBasket();

        $this->basketService->cancelDiscountCode($basket);

        return $this->redirectToRoute('app_basket');
    }

    private function getUserBasket(): RedirectResponse | Basket
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $basket = $user->getBasket();

        if (!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
        }

        return $basket;
    }
}
