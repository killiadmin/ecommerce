<?php

namespace App\Controller;

use App\Repository\DiscountCodeRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardAdministratorController extends AbstractController
{
    #[Route('/dashboard/administrator', name: 'app_dashboard_administrator')]
    #[IsGranted('ROLE_ADMIN')]
    public function viewDashboard(
        ProductRepository      $productRepository,
        UserRepository         $userRepository,
        DiscountCodeRepository $discountCodeRepository
    ): Response
    {
        return $this->render('dashboard_administrator/dashboard.html.twig', [
            'products' => $productRepository->findAll(),
            'users' => $userRepository->findAll(),
            'discounts' => $discountCodeRepository->findAll(),
        ]);
    }
}
