<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    /**
     * @param ProductService $productService
     * @param Request $request
     * @return Response
     * @throws SessionNotFoundException
     */
    #[Route('/', name: 'app_product')]
    public function listProducts(
        ProductService $productService,
        Request        $request,
    ): Response
    {
        $request->getSession()->set('last_page', $request->getUri());
        $data = $productService->getProductsData();

        return $this->render('product/product.html.twig', [
            'products' => $data['products'],
            'categories' => $data['categories'],
            'user' => $data['user'],
        ]);
    }

    /**
     * @param Request $request
     * @param ProductService $productService
     * @return Response
     * @throws LogicException
     * @throws InvalidOptionsException
     */
    #[Route('/product/new', name: 'new_product')]
    public function addProduct(
        Request $request,
        ProductService $productService
    ): Response
    {
        $result = $productService->handleAddProduct($request);

        if ($result['success']) {
            $this->addFlash('success', 'Le produit a été bien été ajoutée.');
            return $this->redirectToRoute('app_dashboard_administrator');
        }

        return $this->render('dashboard_administrator/add_product.html.twig', [
            'AddProduct' => $result['form']->createView(),
        ]);
    }

    /**
     * @param int $id
     * @param ProductService $productService
     * @return Response
     */
    #[Route('/product/delete/{id}', name: 'delete_product')]
    public function deleteProduct(int $id, ProductService $productService): Response
    {
        $result = $productService->handleDeleteProduct($id);

        if (!$result['success']) {
            return new JsonResponse(['success' => false, 'message' => $result['message']], 404);
        }

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_product');
    }
}
