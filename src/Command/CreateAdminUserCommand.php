<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create an Admin user for testing',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Creating Admin User...');

        $repository = $this->entityManager->getRepository(Role::class);
        $adminRole = $repository->findOneBy(['nomRole' => 'Admin']);

        if (!$adminRole) {
            $output->writeln('<error>Role "Admin" not found in database!</error>');
            $output->writeln('Please create it first with: php bin/console doctrine:fixtures:load');
            return Command::FAILURE;
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Admin');
        $utilisateur->setPrenom('User');
        $utilisateur->setEmail('admin@example.com');
        $utilisateur->setMatricule('ADMIN001');
        $utilisateur->setRole($adminRole);
        $utilisateur->setEstActif(true);

        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, 'admin123456');
        $utilisateur->setPassword($hashedPassword);

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $output->writeln('<info>Admin user created successfully!</info>');
        $output->writeln('');
        $output->writeln('Credentials:');
        $output->writeln('  Email: admin@example.com');
        $output->writeln('  Password: admin123456');

        return Command::SUCCESS;
    }
}
