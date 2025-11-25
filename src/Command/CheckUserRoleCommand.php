<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:check-role',
    description: 'Vérifier et afficher le rôle d\'un utilisateur',
)]
class CheckUserRoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('Utilisateur avec l\'email "%s" non trouvé.', $email));
            return Command::FAILURE;
        }

        $io->title('Informations de l\'utilisateur');
        
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['ID', $user->getId()],
                ['Nom', $user->getNom()],
                ['Prénom', $user->getPrenom()],
                ['Email', $user->getEmail()],
                ['Rôle (entité)', $user->getRole() ? $user->getRole()->getNomRole() : 'Aucun'],
                ['Rôles Symfony', implode(', ', $user->getRoles())],
                ['Actif', $user->isEstActif() ? 'Oui' : 'Non'],
            ]
        );

        // Afficher tous les rôles disponibles
        $io->section('Rôles disponibles dans la base de données');
        $roles = $this->entityManager->getRepository(Role::class)->findAll();
        
        $rolesData = [];
        foreach ($roles as $role) {
            $rolesData[] = [
                $role->getId(),
                $role->getNomRole(),
                'ROLE_' . strtoupper(str_replace(' ', '_', $role->getNomRole()))
            ];
        }
        
        $io->table(
            ['ID', 'Nom du Rôle', 'Rôle Symfony'],
            $rolesData
        );

        $io->success('Vérification terminée !');

        return Command::SUCCESS;
    }
}
