<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;

class ProductDetailController extends AbstractController
{
    #[Route('/product/detail/{id<\d+>}', name: 'product_detail')]
    public function viewOneProduct(
        int $id,
        ProductRepository $productRepository
    ): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product_detail/product.html.twig', ['product' => $product]);
    }
}
