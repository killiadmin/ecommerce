<?php

namespace App\Service;

use App\Entity\Basket;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserService
{
    private Security $security;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public function getUserBasket(): RedirectResponse|Basket
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        $basket = $user->getBasket();

        if (!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
        }

        return $basket;
    }
}