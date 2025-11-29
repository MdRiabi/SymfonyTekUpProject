<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjectHistory;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class ProjectHistoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Enregistre une action dans l'historique du projet
     *
     * @param Projet $projet Le projet concerné
     * @param string $action Type d'action (launched, rejected, revision_requested, etc.)
     * @param string|null $remarks Remarques ou commentaires
     * @param Utilisateur|null $performedBy Utilisateur ayant effectué l'action
     * @param string|null $oldStatus Ancien statut (optionnel)
     * @param string|null $newStatus Nouveau statut (optionnel)
     */
    public function log(
        Projet $projet,
        string $action,
        ?string $remarks = null,
        ?Utilisateur $performedBy = null,
        ?string $oldStatus = null,
        ?string $newStatus = null
    ): ProjectHistory {
        $history = new ProjectHistory();
        $history->setProjet($projet);
        $history->setAction($action);
        $history->setRemarks($remarks);
        $history->setPerformedBy($performedBy);
        $history->setOldStatus($oldStatus);
        $history->setNewStatus($newStatus);

        $this->entityManager->persist($history);
        // Note: le flush sera fait par le controller

        return $history;
    }

    /**
     * Récupère l'historique d'un projet
     */
    public function getHistory(Projet $projet, int $limit = 50): array
    {
        return $this->entityManager
            ->getRepository(ProjectHistory::class)
            ->findByProjet($projet->getId(), $limit);
    }
}
