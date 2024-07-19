<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        // Get the logged in user
        $user = $this->security->getUser();

        // Check if a user is logged in
        if ($user) {
            return $this->render('profil/profil.html.twig', [
                'user' => $user,
            ]);
        }

        // Redirect to login if there's no user
        return $this->redirectToRoute('app_login');
    }

    /**
     * Edit user profile.
     *
     * @param Request $request The request object.
     * @param EntityManagerInterface $entityManager The entity manager.
     *
     * @return Response
     */
    #[Route('/profil/edit', name: 'profil_edit')]
    public function editProfil(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $editProfilForm = $this->createForm(EditProfilType::class, $user);
            $editProfilForm->handleRequest($request);

            if ($editProfilForm->isSubmitted() && $editProfilForm->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
            }
        } else {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profil/profil_edit.html.twig', [
            'EditProfil' => $editProfilForm->createView(),
        ]);
    }
}
