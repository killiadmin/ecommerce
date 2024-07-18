<?php

namespace App\DataFixtures;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // User admin
        $userAdmin = new User();
        $userAdmin->setEmail('killiadmin@ecommerce.com');
        $userAdmin->setPassword($this->passwordHasher->hashPassword($userAdmin, 'password'));
        $userAdmin->setFirstname('Killiadmin');
        $userAdmin->setLastname('Administrator');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($userAdmin);

        // Create users

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($user);
            $users[] = $user;
        }

        // Créer les produits
        $products = [];
        for ($i = 1; $i <= 10; $i++) {
            $product = new Product();
            $product->setTitle($faker->text(16));
            $product->setDescription($faker->text(500));
            $product->setPrice($faker->randomFloat(2, 10, 100));
            $product->setCategory($faker->randomElement(['A', 'B', 'C', 'D']));
            $product->setActive($faker->boolean);
            $product->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now')));
            $product->setRentalCounter(0);
            $product->setPicture($faker->imageUrl());

            $manager->persist($product);
            $products[] = $product;
        }

        // Créer les paniers et les items de panier
        for ($i = 1; $i <= 10; $i++) {
            // Récupérer un utilisateur aléatoire
            $user = $users[$faker->numberBetween(0, count($users) - 1)];

            // Créer un panier
            $basket = new Basket();
            $basket->setUser($user);
            $basket->setCreatedAt(new \DateTimeImmutable());
            $basket->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($basket);

            // Créer des items pour le panier
            for ($j = 1; $j <= $faker->numberBetween(1, 5); $j++) {
                // Récupérer un produit aléatoire
                $product = $products[$faker->numberBetween(0, count($products) - 1)];

                $basketItem = new BasketItem();
                $basketItem->setBasket($basket);
                $basketItem->setProduct($product);
                $basketItem->setQuantity($faker->numberBetween(1, 10));
                $basketItem->setPrice($product->getPrice());

                $manager->persist($basketItem);
            }
        }

        $manager->flush();
    }
}
