<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use App\Service\ProjectStatsService;
use App\Service\ProjectHistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/project/{id}/dashboard')]
class ProjectDashboardController extends AbstractController
{
    /**
     * Vue 360° du projet - Dashboard complet
     */
    #[Route('', name: 'admin_project_dashboard_360', methods: ['GET'])]
    public function dashboard(
        Projet $projet,
        ProjectStatsService $statsService,
        ProjectHistoryService $historyService
    ): Response {
        // Récupérer toutes les statistiques
        $stats = $statsService->getAllStats($projet);
        
        // Récupérer l'historique récent (10 dernières actions)
        $recentActivity = $historyService->getHistory($projet, 10);

        return $this->render('admin/project/dashboard_360.html.twig', [
            'projet' => $projet,
            'kpis' => $stats['kpis'],
            'progression_phases' => $stats['progression_phases'],
            'distribution_taches' => $stats['distribution_taches'],
            'performance_equipe' => $stats['performance_equipe'],
            'timeline' => $stats['timeline'],
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * API JSON pour les graphiques (si besoin de rechargement dynamique)
     */
    #[Route('/stats', name: 'admin_project_dashboard_stats_api', methods: ['GET'])]
    public function statsApi(
        Projet $projet,
        ProjectStatsService $statsService
    ): JsonResponse {
        $stats = $statsService->getAllStats($projet);
        
        return $this->json($stats);
    }

    /**
     * API pour l'activité récente (si besoin de rechargement)
     */
    #[Route('/activity', name: 'admin_project_dashboard_activity_api', methods: ['GET'])]
    public function activityApi(
        Projet $projet,
        ProjectHistoryService $historyService
    ): JsonResponse {
        $activity = $historyService->getHistory($projet, 20);
        
        // Formater pour JSON
        $formattedActivity = [];
        foreach ($activity as $entry) {
            $formattedActivity[] = [
                'id' => $entry->getId(),
                'action' => $entry->getAction(),
                'remarks' => $entry->getRemarks(),
                'performedBy' => $entry->getPerformedBy() ? [
                    'nom' => $entry->getPerformedBy()->getNom(),
                    'prenom' => $entry->getPerformedBy()->getPrenom(),
                ] : null,
                'createdAt' => $entry->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json($formattedActivity);
    }
}
