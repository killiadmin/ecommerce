<?php

namespace App\Controller;

use App\Entity\DiscountCode;
use App\Form\DiscountCodeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DiscountCodeController extends AbstractController
{
    /**
     * Adds a discount code to the system.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    #[Route('/discount/code', name: 'app_discount_code')]
    public function addDiscountCode(
        Request                $request,
        EntityManagerInterface $em
    ): Response
    {
        $code = new DiscountCode();
        $addDiscountCodeForm = $this->createForm(DiscountCodeType::class, $code);
        $addDiscountCodeForm->handleRequest($request);

        if ($addDiscountCodeForm->isSubmitted() && $addDiscountCodeForm->isValid()) {
            $code->setNbUsage(0);
            $code->setCreatedAt(new \DateTimeImmutable());

            $em->persist($code);
            $em->flush();

            $this->addFlash('success', 'Le code a été bien été ajoutée.');
            return $this->redirectToRoute('app_dashboard_administrator');
        }

        return $this->render('discount_code/add_discount_code.html.twig', [
            'AddDiscountCode' => $addDiscountCodeForm->createView(),
        ]);
    }
}
