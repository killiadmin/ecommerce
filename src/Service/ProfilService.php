<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;

class ProfilService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Toggles the current account mode for the authenticated user between professional and non-professional.
     *
     * @return array
     */
    public function toggleAccountMode(): array
    {
        $user = $this->security->getUser();

        if (! $user instanceof User) {
            return ['success' => false, 'status' => Response::HTTP_NOT_FOUND];
        }

        $user->setProfessional(!$user->isProfessional());
        $this->entityManager->flush();

        return [
            'success' => true,
            'isProfessional' => $user->isProfessional(),
            'status' => Response::HTTP_OK
        ];
    }
}
