<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardAdministratorController extends AbstractController
{
    #[Route('/dashboard/administrator', name: 'app_dashboard_administrator')]
    public function viewDashboard(): Response
    {
        return $this->render('dashboard_administrator/dashboard.html.twig', [
            'controller_name' => 'DashboardAdministratorController',
        ]);
    }
}
