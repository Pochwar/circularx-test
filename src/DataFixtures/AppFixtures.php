<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Takeover;
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
        $products = $this->createProducts($manager, $brands);

        // Create Users
        $users = $this->createUsers($manager);

        $this->createTakeovers($manager, $products, $users);
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

    #[ArrayShape(['galaxy-s-22' => "\App\Entity\Product", 'galaxy-z-fold-2' => "\App\Entity\Product", 'one-plus-10-pro' => "\App\Entity\Product", 'one-plus-nord-2-t' => "\App\Entity\Product"])]
    private function createProducts(ObjectManager $manager, array $brands): array
    {
        $galaxyS22 = Product::create($brands['samsung'], 'Galaxy S22', 119999);
        $manager->persist($galaxyS22);

        $galaxyZFold2 = Product::create($brands['samsung'], 'Galaxy Z Fold 2', 89999);
        $manager->persist($galaxyZFold2);

        $onePlus10Pro = Product::create($brands['one-plus'], 'OnePlus 10 Pro', 91900);
        $manager->persist($onePlus10Pro);

        $onePlusNord2T = Product::create($brands['one-plus'], 'OnePlus Nord 2T', 42900);
        $manager->persist($onePlusNord2T);

        $manager->flush();

        return [
            'galaxy-s-22' => $galaxyS22,
            'galaxy-z-fold-2' => $galaxyZFold2,
            'one-plus-10-pro' => $onePlus10Pro,
            'one-plus-nord-2-t' => $onePlusNord2T,
        ];
    }

    #[ArrayShape(['user-1' => "\App\Entity\User", 'user-2' => "\App\Entity\User"])]
    private function createUsers(ObjectManager $manager): array
    {
        $user1 = User::create('user1@test-circularx.com');
        $manager->persist($user1);

        $user2 = User::create('user2@test-circularx.com');
        $manager->persist($user2);

        $manager->flush();

        return [
            'user-1' => $user1,
            'user-2' => $user2,
        ];
    }

    private function createTakeovers(ObjectManager $manager, array $products, array $users): void
    {
        $takeover1 = Takeover::create($users['user-1'], [
            $products['galaxy-s-22'],
            $products['galaxy-z-fold-2'],
            $products['one-plus-nord-2-t'],
        ]);
        $manager->persist($takeover1);

        $takeover2 = Takeover::create($users['user-2'], [
            $products['one-plus-10-pro'],
            $products['galaxy-z-fold-2'],
        ]);
        $manager->persist($takeover2);

        $manager->flush();
    }
}
