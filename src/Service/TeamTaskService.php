<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\Tache;
use App\Repository\TacheRepository;
use App\Repository\TimeEntryRepository;

class TeamTaskService
{
    public function __construct(
        private TacheRepository $tacheRepository,
        private TimeEntryRepository $timeEntryRepository
    ) {
    }

    /**
     * Récupère toutes les tâches d'un utilisateur
     */
    public function getMyTasks(Utilisateur $user): array
    {
        return $this->tacheRepository->findBy(
            ['assigne' => $user],
            ['deadline' => 'ASC']
        );
    }

    /**
     * Récupère les tâches filtrées
     */
    public function getMyTasksFiltered(Utilisateur $user, array $filters = []): array
    {
        $qb = $this->tacheRepository->createQueryBuilder('t')
            ->where('t.assigne = :user')
            ->setParameter('user', $user);

        // Filtre par statut
        if (isset($filters['statut']) && $filters['statut'] !== 'all') {
            $qb->andWhere('t.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        // Filtre par projet
        if (isset($filters['projet']) && $filters['projet']) {
            $qb->join('t.phase', 'p')
               ->andWhere('p.projet = :projet')
               ->setParameter('projet', $filters['projet']);
        }

        // Filtre par priorité
        if (isset($filters['priorite']) && $filters['priorite'] !== 'all') {
            $qb->andWhere('t.priorite = :priorite')
               ->setParameter('priorite', $filters['priorite']);
        }

        // Tri
        $orderBy = $filters['orderBy'] ?? 'deadline';
        $order = $filters['order'] ?? 'ASC';
        $qb->orderBy('t.' . $orderBy, $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * Calcule les KPIs personnels d'un utilisateur
     */
    public function getPersonalKPIs(Utilisateur $user): array
    {
        $allTasks = $this->getMyTasks($user);
        
        $totalTasks = count($allTasks);
        $inProgressTasks = 0;
        $completedTasks = 0;
        $urgentTasks = [];

        foreach ($allTasks as $task) {
            if ($task->getStatut() === 'EN_COURS') {
                $inProgressTasks++;
            } elseif ($task->getStatut() === 'TERMINE') {
                $completedTasks++;
            }

            // Tâches urgentes (deadline < 3 jours)
            if ($task->getDeadline()) {
                $now = new \DateTime();
                $diff = $now->diff($task->getDeadline());
                if ($diff->days <= 3 && !$diff->invert && $task->getStatut() !== 'TERMINE') {
                    $urgentTasks[] = $task;
                }
            }
        }

        // Trier les tâches urgentes par deadline
        usort($urgentTasks, function($a, $b) {
            return $a->getDeadline() <=> $b->getDeadline();
        });

        // Calculer le taux de complétion
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Temps travaillé aujourd'hui
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $timeToday = $this->timeEntryRepository->getTotalTimeByUser($user->getId(), $today, $tomorrow);

        return [
            'total_tasks' => $totalTasks,
            'in_progress' => $inProgressTasks,
            'completed' => $completedTasks,
            'completion_rate' => $completionRate,
            'urgent_tasks' => array_slice($urgentTasks, 0, 3), // Top 3
            'time_today' => $timeToday, // en secondes
        ];
    }

    /**
     * Récupère les projets d'un utilisateur
     */
    public function getMyProjects(Utilisateur $user): array
    {
        $tasks = $this->getMyTasks($user);
        $projects = [];

        foreach ($tasks as $task) {
            if ($task->getPhase() && $task->getPhase()->getProjet()) {
                $projet = $task->getPhase()->getProjet();
                $projetId = $projet->getId();

                if (!isset($projects[$projetId])) {
                    $projects[$projetId] = [
                        'projet' => $projet,
                        'tasks_count' => 0,
                        'completed_count' => 0,
                    ];
                }

                $projects[$projetId]['tasks_count']++;
                if ($task->getStatut() === 'TERMINE') {
                    $projects[$projetId]['completed_count']++;
                }
            }
        }

        // Calculer la progression pour chaque projet
        foreach ($projects as &$project) {
            $project['progression'] = $project['tasks_count'] > 0
                ? round(($project['completed_count'] / $project['tasks_count']) * 100, 1)
                : 0;
        }

        return array_values($projects);
    }

    /**
     * Formate une durée en secondes en texte lisible
     */
    public function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return sprintf('%dh%02d', $hours, $minutes);
        }

        return sprintf('%dmin', $minutes);
    }
}
