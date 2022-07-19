<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use JetBrains\PhpStorm\ArrayShape;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Brands
        $brands = $this->createBrands($manager);

        // Create Products
        $this->createProducts($manager, $brands);

        // Create Users
        $this->createUsers($manager);
    }

    #[ArrayShape(['samsung' => "\App\Entity\Brand", 'one-plus' => "\App\Entity\Brand"])]
    private function createBrands(ObjectManager $manager): array
    {
        $samsung = Brand::create('Samsung');
        $manager->persist($samsung);

        $onePlus = Brand::create('One Plus');
        $manager->persist($onePlus);

        $manager->flush();

        return [
            'samsung' => $samsung,
            'one-plus' => $onePlus
        ];
    }

    private function createProducts(ObjectManager $manager, array $brands): void
    {
        $galaxyS22 = Product::create($brands['samsung'], 'Galaxy S22', 119999);
        $manager->persist($galaxyS22);

        $galaxyZFold2 = Product::create($brands['samsung'], 'Galaxy Z Fold 2', 89999);
        $manager->persist($galaxyZFold2);

        $onePLus10Pro = Product::create($brands['one-plus'], 'OnePlus 10 Pro', 91900);
        $manager->persist($onePLus10Pro);

        $onePLusNord2T = Product::create($brands['one-plus'], 'OnePlus Nord 2T', 42900);
        $manager->persist($onePLusNord2T);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): void
    {
        $user1 = User::create('user1@test-circularx.com');
        $manager->persist($user1);

        $user2 = User::create('user2@test-circularx.com');
        $manager->persist($user2);

        $manager->flush();
    }
}
