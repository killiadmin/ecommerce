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
        $userAdmin->setAgree(1);
        $userAdmin->setAvatar('avatar_001.png');
        $userAdmin->setProfessional(true);
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
            $user->setAgree(1);
            $user->setAvatar('avatar_OO1.png');
            $user->setProfessional(false);
            $user->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($user);
            $users[] = $user;
        }

        // Create the products
        $products = [];

        // List category
        $categories = [
            'Électronique',
            'Mode et Vêtements',
            'Beauté et Soins Personnels',
            'Maison et Décoration',
            'Alimentation et Boissons',
            'Sport et Loisirs',
            'Jouets et Jeux',
            'Santé et Bien-être',
            'Auto et Moto',
            'Livres et Papeterie',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();
            $product->setTitle($faker->text(16));
            $product->setDescription($faker->text(500));
            $product->setPrice(99.99);
            $product->setCategory($faker->randomElement($categories));
            $product->setActive($faker->boolean);
            $product->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now')));
            $product->setRentalCounter(0);
            $product->setPicture('img/no_picture.jpg');

            $manager->persist($product);
            $products[] = $product;
        }

        foreach(array_merge([$userAdmin], $users) as $user) {
            $basket = new Basket();
            $basket->setUser($user);
            $basket->setCreatedAt(new \DateTimeImmutable());
            $basket->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($basket);

            // Create items for the cart
            for ($j = 1; $j <= $faker->numberBetween(1, 5); $j++) {
                // Collect a random product
                $product = $products[$faker->numberBetween(0, count($products) - 1)];

                $basketItem = new BasketItem();
                $basketItem->setBasket($basket);
                $basketItem->setProduct($product);
                $basketItem->setQuantity($faker->numberBetween(1, 10));
                $basketItem->setPrice($product->getPrice());
                $basketItem->setPriceTva($product->getPrice() * 1.2);

                $manager->persist($basketItem);
            }
        }

        $manager->flush();
    }
}
