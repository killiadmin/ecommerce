<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $products = $productRepository->findAllProduct();

        $categories = [];
        foreach ($products as $product) {
            if (!in_array($product->getCategory(), $categories, true)) {
                $categories[] = $product->getCategory();
            }
        }

        return $this->render('product/product.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }


    /**
     * Handles the creation and submission of a new product form.
     *
     * @param Request $request The current request instance.
     * @param EntityManagerInterface $em The entity manager for database operations.
     * @return Response The response object to be sent to the client.
     */
    #[Route('/product/new', name: 'new_product')]
    public function addProduct(
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $product = new Product();
        $addProductForm = $this->createForm(AddProductType::class, $product);
        $addProductForm->handleRequest($request);

        if ($addProductForm->isSubmitted() && $addProductForm->isValid()) {
            $product->setRentalCounter(0);
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setStock(0);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Le produit a été bien été ajoutée.');
            return $this->redirectToRoute('app_dashboard_administrator');
        }

        return $this->render('dashboard_administrator/add_product.html.twig', [
            'AddProduct' => $addProductForm->createView(),
        ]);
    }

    #[Route('/product/delete/{id}', name: 'delete_product')]
    public function deleteProduct($id, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found!');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_product');
    }
}
