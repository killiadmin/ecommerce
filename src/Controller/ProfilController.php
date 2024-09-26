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

    /**
     * Profil user
     *
     * @return Response
     */
    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        $user = $this->security->getUser();

        if ($user) {
            return $this->render('profil/profil.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * Handles the profile editing process for a logged-in user.
     *
     * This method retrieves the current user, generates a list of available avatar choices,
     * and processes the profile edit form. If the form is successfully submitted and valid,
     * the user's data is persisted in the database, and the user is redirected to their profile page.
     *
     * @param Request $request The HTTP request instance containing form data.
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
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
            $avatarDir = __DIR__ . '/../../public/img/avatar/';
            $avatars = array_map('basename', glob($avatarDir . 'avatar_*.png'));

            $avatarsChoices = [];
            foreach ($avatars as $avatar) {
                $avatarName = pathinfo($avatar, PATHINFO_FILENAME);
                $avatarsChoices[$avatarName] = $avatar;
            }

            $editProfilForm = $this->createForm(EditProfilType::class, $user, [
                'avatars' => $avatarsChoices
            ]);

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
