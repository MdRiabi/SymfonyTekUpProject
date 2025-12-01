<?php

namespace App\Service;

use App\Entity\TimeEntry;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Repository\TimeEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class TimeTrackingService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TimeEntryRepository $timeEntryRepository
    ) {
    }

    /**
     * Démarre un nouveau timer pour une tâche
     */
    public function startTimer(Tache $tache, Utilisateur $user): TimeEntry
    {
        // Vérifier qu'il n'y a pas déjà un timer actif
        $activeTimer = $this->getActiveTimer($user);
        if ($activeTimer) {
            throw new \Exception("Un timer est déjà actif. Veuillez l'arrêter avant d'en démarrer un nouveau.");
        }

        // Créer une nouvelle entrée de temps
        $entry = new TimeEntry();
        $entry->setTache($tache);
        $entry->setUtilisateur($user);
        $entry->setDateDebut(new \DateTime());
        $entry->setType('TIMER');

        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    /**
     * Arrête le timer en cours
     */
    public function stopTimer(Utilisateur $user): ?TimeEntry
    {
        $activeTimer = $this->getActiveTimer($user);
        
        if (!$activeTimer) {
            return null;
        }

        // Définir la date de fin
        $activeTimer->setDateFin(new \DateTime());
        
        // La durée est calculée automatiquement dans setDateFin()
        
        $this->em->flush();

        return $activeTimer;
    }

    /**
     * Met en pause le timer (alias de stopTimer pour clarté)
     */
    public function pauseTimer(Utilisateur $user): ?TimeEntry
    {
        return $this->stopTimer($user);
    }

    /**
     * Récupère le timer actif de l'utilisateur
     */
    public function getActiveTimer(Utilisateur $user): ?TimeEntry
    {
        return $this->timeEntryRepository->findActiveEntry($user->getId());
    }

    /**
     * Calcule le temps total travaillé sur une tâche par un utilisateur
     */
    public function getTotalTimeOnTask(Tache $tache, Utilisateur $user): int
    {
        $entries = $this->timeEntryRepository->findBy([
            'tache' => $tache,
            'utilisateur' => $user
        ]);

        $totalSeconds = 0;
        foreach ($entries as $entry) {
            if ($entry->getDuree()) {
                $totalSeconds += $entry->getDuree();
            }
        }

        return $totalSeconds;
    }

    /**
     * Récupère toutes les sessions de travail sur une tâche
     */
    public function getTimeSessions(Tache $tache, Utilisateur $user): array
    {
        return $this->timeEntryRepository->findBy(
            [
                'tache' => $tache,
                'utilisateur' => $user
            ],
            ['dateDebut' => 'DESC']
        );
    }

    /**
     * Formate une durée en secondes en format lisible
     */
    public function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dmin', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%dmin %02ds', $minutes, $secs);
        } else {
            return sprintf('%ds', $secs);
        }
    }

    /**
     * Vérifie si un utilisateur a un timer actif
     */
    public function hasActiveTimer(Utilisateur $user): bool
    {
        return $this->getActiveTimer($user) !== null;
    }

    /**
     * Récupère le temps écoulé depuis le démarrage du timer actif
     */
    public function getElapsedTime(Utilisateur $user): int
    {
        $activeTimer = $this->getActiveTimer($user);
        
        if (!$activeTimer) {
            return 0;
        }

        $now = new \DateTime();
        $diff = $now->getTimestamp() - $activeTimer->getDateDebut()->getTimestamp();
        
        return $diff;
    }
}
