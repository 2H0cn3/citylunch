<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $plats = [
            ['Bœuf bourguignon', 'Plat traditionnel mijoté au vin rouge', '12.50'],
            ['Saumon en croûte', 'Filet de saumon, croûte aux herbes', '14.00'],
            ['Curry de légumes', 'Curry végétarien et riz basmati', '11.00'],
        ];
        foreach ($plats as [$n, $d, $p]) {
            $manager->persist((new Product())
                ->setName($n)->setDescription($d)
                ->setPrice($p)->setType(Product::TYPE_PLAT));
        }

        $desserts = [
            ['Tarte Tatin', 'Pommes caramélisées', '5.50'],
            ['Mousse au chocolat', 'Chocolat noir 70%', '4.50'],
        ];
        foreach ($desserts as [$n, $d, $p]) {
            $manager->persist((new Product())
                ->setName($n)->setDescription($d)
                ->setPrice($p)->setType(Product::TYPE_DESSERT));
        }

        $demo = (new Customer())
            ->setEmail('demo@citylunch.test')
            ->setFirstName('Demo')
            ->setLastName('User')
            ->setAddress('1 rue de la République, 31000 Toulouse')
            ->setPhone('0600000000');
        $demo->setPassword($this->hasher->hashPassword($demo, 'demopass123'));
        $manager->persist($demo);

        $manager->flush();
    }
}