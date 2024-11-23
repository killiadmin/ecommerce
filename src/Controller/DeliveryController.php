<?php

namespace App\Controller;

use App\Entity\UserAddress;
use App\Form\DeliveryType;
use App\Repository\UserAddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeliveryController extends AbstractController
{
    /**
     * Manages the delivery address for the logged-in user and renders the delivery template.
     *
     * @param UserAddressRepository $userAddressRepository
     * @param Security $security
     * @return Response
     */
    #[Route('/livraison', name: 'app_delivery')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function managementDelivery(
        UserAddressRepository $userAddressRepository,
        Security              $security
    ): Response
    {
        $user = $security->getUser();
        $address = $userAddressRepository->findOneBy(['user_associated' => $user]);

        return $this->render('delivery/delivery.html.twig', [
            'address' => $address,
        ]);
    }

    /**
     * Adds a delivery address for the authenticated user. If the user already has an address,
     * it will be edited; otherwise, a new address will be created.
     *
     * @param Request $request
     * @param Security $security
     * @param UserAddressRepository $userAddressRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    #[Route('/adresse-livraison/ajout', name: 'app_delivery_add')]
    public function addDelivery(
        Request                $request,
        Security               $security,
        UserAddressRepository  $userAddressRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $security->getUser();

        $existingAddress = $userAddressRepository->findOneBy(['user_associated' => $user]);

        if ($existingAddress !== null) {
            $userAddress = $existingAddress;
        } else {
            $userAddress = new UserAddress();
            $userAddress->setUserAssociated($user);
        }

        $form = $this->createForm(DeliveryType::class, $userAddress);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$form->get('billing')->getData()) {
                $data->setNumberBilling($data->getNumberDelivery());
                $data->setLibelleBilling($data->getLibelleDelivery());
                $data->setCodeBilling($data->getCodeDelivery());
                $data->setCityBilling($data->getCityDelivery());
            }

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute('app_delivery');
        }

        return $this->render('delivery/add_delivery.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
