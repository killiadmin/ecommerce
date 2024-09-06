<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function listProducts(
        ProductRepository $productRepository,
        Request           $request
    ): Response
    {
        $request->getSession()->set('last_page', $request->getUri());

        return $this->render('product/product.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
}
