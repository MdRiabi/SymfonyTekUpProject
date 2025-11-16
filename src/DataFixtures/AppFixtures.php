<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $adminRole = new Role();
        $adminRole->setNomRole('Admin');
        $adminRole->setDescription('Administrateur du système');
        $manager->persist($adminRole);

        $userRole = new Role();
        $userRole->setNomRole('User');
        $userRole->setDescription('Utilisateur standard');
        $manager->persist($userRole);

        $chefProjetRole = new Role();
        $chefProjetRole->setNomRole('Chef de Projet');
        $chefProjetRole->setDescription('Chef de projet');
        $manager->persist($chefProjetRole);

        $manager->flush();

        $adminUser = new Utilisateur();
        $adminUser->setNom('Admin');
        $adminUser->setPrenom('User');
        $adminUser->setEmail('admin@example.com');
        $adminUser->setMatricule('ADMIN001');
        $adminUser->setRole($adminRole);
        $adminUser->setEstActif(true);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'admin123456');
        $adminUser->setPassword($hashedPassword);
        $manager->persist($adminUser);

        $manager->flush();
    }
}
