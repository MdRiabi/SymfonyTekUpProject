<?php

namespace App\Service;

use App\Entity\Projet;
use App\Repository\ProjetRepository;

class ProjectStatsService
{
    public function __construct(
        private ProjetRepository $projetRepository
    ) {
    }

    /**
     * Calcule les KPIs principaux du projet
     * 
     * @return array [
     *   'progression' => float (0-100),
     *   'membres' => int,
     *   'taches_total' => int,
     *   'taches_terminees' => int,
     *   'jours_restants' => int|null
     * ]
     */
    public function getProjectKPIs(Projet $projet): array
    {
        $totalTaches = $projet->getTaches()->count();
        $tachesTerminees = 0;
        $tachesEnCours = 0;
        $tachesAFaire = 0;

        // Compter les tâches par statut
        foreach ($projet->getTaches() as $tache) {
            if ($tache->getStatut() === 'TERMINE') {
                $tachesTerminees++;
            } elseif ($tache->getStatut() === 'EN_COURS') {
                $tachesEnCours++;
            } else {
                $tachesAFaire++;
            }
        }

        // Calculer la progression (% de tâches terminées)
        $progression = $totalTaches > 0 ? round(($tachesTerminees / $totalTaches) * 100, 1) : 0;

        // Compter les membres uniques assignés
        $membres = [];
        foreach ($projet->getTaches() as $tache) {
            if ($tache->getAssigne() && !in_array($tache->getAssigne()->getId(), $membres)) {
                $membres[] = $tache->getAssigne()->getId();
            }
        }

        // Calculer les jours restants jusqu'à la deadline
        $joursRestants = null;
        // Check if method exists before calling
        if (method_exists($projet, 'getDateFinPrevue') && $projet->getDateFinPrevue()) {
            $now = new \DateTime();
            $interval = $now->diff($projet->getDateFinPrevue());
            $joursRestants = $interval->invert ? -$interval->days : $interval->days;
        }

        return [
            'progression' => $progression,
            'membres' => count($membres),
            'taches_total' => $totalTaches,
            'taches_terminees' => $tachesTerminees,
            'taches_en_cours' => $tachesEnCours,
            'taches_a_faire' => $tachesAFaire,
            'jours_restants' => $joursRestants,
        ];
    }

    /**
     * Récupère la progression par phase
     * 
     * @return array [
     *   ['nom' => string, 'progression' => float, 'taches' => int, 'terminees' => int],
     *   ...
     * ]
     */
    public function getProgressionByPhase(Projet $projet): array
    {
        $phases = [];

        foreach ($projet->getPhases() as $phase) {
            $totalTaches = 0;
            $tachesTerminees = 0;

            foreach ($projet->getTaches() as $tache) {
                if ($tache->getPhase() && $tache->getPhase()->getId() === $phase->getId()) {
                    $totalTaches++;
                    if ($tache->getStatut() === 'TERMINE') {
                        $tachesTerminees++;
                    }
                }
            }

            $progression = $totalTaches > 0 ? round(($tachesTerminees / $totalTaches) * 100, 1) : 0;

            $phases[] = [
                'nom' => $phase->getNom(),
                'progression' => $progression,
                'taches' => $totalTaches,
                'terminees' => $tachesTerminees,
                'ordre' => method_exists($phase, 'getOrdre') ? $phase->getOrdre() : 0,
            ];
        }

        // Trier par ordre
        usort($phases, fn($a, $b) => $a['ordre'] <=> $b['ordre']);

        return $phases;
    }

    /**
     * Récupère la distribution des tâches par statut
     * 
     * @return array ['labels' => array, 'data' => array, 'colors' => array]
     */
    public function getTasksDistribution(Projet $projet): array
    {
        $stats = [
            'A_FAIRE' => 0,
            'EN_COURS' => 0,
            'TERMINE' => 0,
        ];

        foreach ($projet->getTaches() as $tache) {
            $statut = $tache->getStatut() ?? 'A_FAIRE';
            if (isset($stats[$statut])) {
                $stats[$statut]++;
            }
        }

        return [
            'labels' => ['À faire', 'En cours', 'Terminées'],
            'data' => [$stats['A_FAIRE'], $stats['EN_COURS'], $stats['TERMINE']],
            'colors' => ['#ef4444', '#f59e0b', '#10b981'], // Rouge, Orange, Vert
        ];
    }

    /**
     * Récupère la performance de l'équipe
     * 
     * @return array [
     *   ['membre' => Utilisateur, 'taches' => int, 'terminees' => int, 'progression' => float],
     *   ...
     * ]
     */
    public function getTeamPerformance(Projet $projet): array
    {
        $performance = [];
        $membresStats = [];

        // Collecter les stats par membre
        foreach ($projet->getTaches() as $tache) {
            $assigne = $tache->getAssigne();
            if (!$assigne) {
                continue;
            }

            $membreId = $assigne->getId();
            if (!isset($membresStats[$membreId])) {
                $membresStats[$membreId] = [
                    'membre' => $assigne,
                    'taches' => 0,
                    'terminees' => 0,
                ];
            }

            $membresStats[$membreId]['taches']++;
            if ($tache->getStatut() === 'TERMINE') {
                $membresStats[$membreId]['terminees']++;
            }
        }

        // Calculer la progression pour chaque membre
        foreach ($membresStats as $stats) {
            $progression = $stats['taches'] > 0 
                ? round(($stats['terminees'] / $stats['taches']) * 100, 1) 
                : 0;

            $performance[] = [
                'membre' => $stats['membre'],
                'taches' => $stats['taches'],
                'terminees' => $stats['terminees'],
                'progression' => $progression,
            ];
        }

        // Trier par nombre de tâches (décroissant)
        usort($performance, fn($a, $b) => $b['taches'] <=> $a['taches']);

        return $performance;
    }

    /**
     * Récupère les données pour la timeline
     * 
     * @return array [
     *   'phases' => array,
     *   'taches_par_phase' => array
     * ]
     */
    public function getTimelineData(Projet $projet): array
    {
        $phases = [];
        $tachesParPhase = [];

        foreach ($projet->getPhases() as $phase) {
            $phaseData = [
                'id' => $phase->getId(),
                'nom' => $phase->getNom(),
                'ordre' => method_exists($phase, 'getOrdre') ? $phase->getOrdre() : 0,
                'dateDebut' => method_exists($phase, 'getDateDebut') ? $phase->getDateDebut() : null,
                'dateFin' => method_exists($phase, 'getDateFin') ? $phase->getDateFin() : null,
            ];

            $phases[] = $phaseData;

            // Récupérer les tâches de cette phase
            $taches = [];
            foreach ($projet->getTaches() as $tache) {
                if ($tache->getPhase() && $tache->getPhase()->getId() === $phase->getId()) {
                    $taches[] = [
                        'id' => $tache->getId(),
                        'nom' => $tache->getNom(),
                        'statut' => $tache->getStatut(),
                        'priorite' => $tache->getPriorite(),
                        'deadline' => $tache->getDeadline(),
                        'assigne' => $tache->getAssigne() ? [
                            'nom' => $tache->getAssigne()->getNom(),
                            'prenom' => $tache->getAssigne()->getPrenom(),
                        ] : null,
                    ];
                }
            }

            $tachesParPhase[$phase->getId()] = $taches;
        }

        // Trier les phases par ordre
        usort($phases, fn($a, $b) => $a['ordre'] <=> $b['ordre']);

        return [
            'phases' => $phases,
            'taches_par_phase' => $tachesParPhase,
        ];
    }

    /**
     * Récupère toutes les stats en une seule fois
     */
    public function getAllStats(Projet $projet): array
    {
        return [
            'kpis' => $this->getProjectKPIs($projet),
            'progression_phases' => $this->getProgressionByPhase($projet),
            'distribution_taches' => $this->getTasksDistribution($projet),
            'performance_equipe' => $this->getTeamPerformance($projet),
            'timeline' => $this->getTimelineData($projet),
        ];
    }
}
