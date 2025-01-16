<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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
        // Ajout d'un utilisateur admin
        $admin = new User();
        $admin->setName('Admin')
              ->setEmail('admin@example.com')
              ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'))
              ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Ajout d'un utilisateur client
        $user = new User();
        $user->setName('Client')
             ->setEmail('client@example.com')
             ->setPassword($this->passwordHasher->hashPassword($user, 'client123'))
             ->setRoles(['ROLE_USER']);
        $manager->persist($user);

        // Ajout de produits
        $products = [
            ['Blackbelt', 29.90, 'M', 10, true],
            ['Pokeball', 45.00, 'L', 8, true],
            ['Street', 34.50, 'XL', 5, false],
            ['PinkLady', 29.90, 'S', 20, false],
        ];

        foreach ($products as [$name, $price, $size, $stock, $isHighlighted]) {
            $product = new Product();
            $product->setName($name)
                    ->setPrice($price)
                    ->setSize($size)
                    ->setStock($stock)
                    ->setIsHighlighted($isHighlighted)
                    ->setImage(null); // Ajouter un chemin ou une URL si nécessaire
            $manager->persist($product);
        }

        // Sauvegarder en base
        $manager->flush();
    }
}
