<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $data = [
            ['Fallout', 199],
            ['Don\'t Starve', 299],
            ['Baldur\'s Gate', 399],
            ['Icewind Dale', 499],
            ['Bloodborne', 599]
        ];

        foreach ($data as $row) {
            $product = new Product();
            $product->setTitle($row[0])
                ->setPrice($row[1]);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
