<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function listProducts(
        ProductRepository $productRepository
    ): Response
    {
        return $this->render('product/product.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
}
