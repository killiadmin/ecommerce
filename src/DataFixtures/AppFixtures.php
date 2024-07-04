<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

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
        }

        $manager->flush();
    }
}
