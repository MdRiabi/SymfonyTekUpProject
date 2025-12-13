<?php

namespace App\Service;

use App\Entity\Projet;
use App\Repository\ProjetRepository;

class ProjectService
{
    public function __construct(
        private ProjetRepository $projetRepository
    ) {
    }

    /**
     * Compte le nombre total de projets configurés (CONFIGURE ou ACTIF)
     */
    public function getConfiguredProjectsCount(): int
    {
        return $this->projetRepository->count([
            'statut' => [Projet::STATUT_CONFIGURE, Projet::STATUT_ACTIF]
        ]);
    }

    /**
     * Compte les projets actifs
     */
    public function getActiveProjectsCount(): int
    {
        return $this->projetRepository->count([
            'statut' => [
                Projet::STATUT_EN_CONFIGURATION,
                Projet::STATUT_CONFIGURE,
                Projet::STATUT_ACTIF,
                Projet::STATUT_EN_COURS
            ]
        ]);
    }

    /**
     * Compte les projets terminés
     */
    public function getCompletedProjectsCount(): int
    {
        return $this->projetRepository->count([
            'statut' => Projet::STATUT_TERMINE
        ]);
    }

    /**
     * Vérifie s'il existe au moins un projet configuré
     */
    public function hasConfiguredProjects(): bool
    {
        return $this->getConfiguredProjectsCount() > 0;
    }

    /**
     * Récupère les projets par statut
     * 
     * @param string|array $status
     * @return Projet[]
     */
    public function getProjectsByStatus(string|array $status): array
    {
        if (is_string($status)) {
            $status = [$status];
        }

        return $this->projetRepository->findBy(
            ['statut' => $status],
            ['dateModification' => 'DESC']
        );
    }

    /**
     * Récupère les projets actifs
     * 
     * @return Projet[]
     */
    public function getActiveProjects(): array
    {
        return $this->getProjectsByStatus([
            Projet::STATUT_EN_CONFIGURATION,
            Projet::STATUT_CONFIGURE,
            Projet::STATUT_ACTIF,
            Projet::STATUT_EN_COURS
        ]);
    }

    /**
     * Récupère les projets terminés
     * 
     * @return Projet[]
     */
    public function getCompletedProjects(): array
    {
        return $this->getProjectsByStatus(Projet::STATUT_TERMINE);
    }

    /**
     * Récupère tous les projets configurés
     * 
     * @return Projet[]
     */
    public function getAllConfiguredProjects(): array
    {
        return $this->getProjectsByStatus([
            Projet::STATUT_CONFIGURE,
            Projet::STATUT_ACTIF,
            Projet::STATUT_TERMINE
        ]);
    }

    /**
     * Récupère les projets récents (limité)
     * 
     * @param int $limit
     * @return Projet[]
     */
    public function getRecentProjects(int $limit = 5): array
    {
        return $this->projetRepository->findBy(
            ['statut' => [Projet::STATUT_CONFIGURE, Projet::STATUT_ACTIF, Projet::STATUT_TERMINE]],
            ['dateModification' => 'DESC'],
            $limit
        );
    }

    /**
     * Récupère les statistiques des projets
     * 
     * @return array
     */
    public function getProjectStats(): array
    {
        return [
            'total' => $this->getConfiguredProjectsCount(),
            'active' => $this->getActiveProjectsCount(),
            'completed' => $this->getCompletedProjectsCount(),
        ];
    }
}
