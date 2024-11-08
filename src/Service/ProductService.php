<?php

namespace App\Service;

use App\Entity\Product;
use App\Form\AddProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Bundle\SecurityBundle\Security;

class ProductService
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;
    private FormFactoryInterface $formFactory;
    private Security $security;

    public function __construct(
        ProductRepository      $productRepository,
        EntityManagerInterface $em,
        FormFactoryInterface   $formFactory,
        Security $security,
    ){
        $this->productRepository = $productRepository;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->security = $security;
    }

    /**
     * Retrieves product data along with their categories.
     *
     * @return array
     */
    public function getProductsData(): array
    {
        $user = $this->security->getUser();
        $products = $this->productRepository->findAllProduct();

        $categories = [];
        foreach ($products as $product) {
            if (!in_array($product->getCategory(), $categories, true)) {
                $categories[] = $product->getCategory();
            }
        }

        return [
            'user' => $user,
            'products' => $products,
            'categories' => $categories,
        ];
    }


    /**
     * @param Request $request
     * @return array
     * @throws LogicException
     * @throws InvalidOptionsException
     */
    public function handleAddProduct(Request $request): array
    {
        $product = new Product();
        $form = $this->formFactory->create(AddProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setRentalCounter(0);
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setStock(0);

            $this->em->persist($product);
            $this->em->flush();

            return [
                'success' => true,
                'product' => $product,
            ];
        }

        return [
            'success' => false,
            'form' => $form,
        ];
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function handleDeleteProduct(int|string $id): array
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found',
            ];
        }

        $this->em->remove($product);
        $this->em->flush();

        return [
            'success' => true,
        ];
    }
}
