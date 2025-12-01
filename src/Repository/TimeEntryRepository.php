<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    /**
     * Récupère les entrées de temps pour un utilisateur
     */
    public function findByUser($userId, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.utilisateur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.dateDebut', 'DESC');

        if ($start) {
            $qb->andWhere('t.dateDebut >= :start')
               ->setParameter('start', $start);
        }

        if ($end) {
            $qb->andWhere('t.dateDebut <= :end')
               ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère le temps total pour un utilisateur sur une période
     */
    public function getTotalTimeByUser($userId, \DateTime $start, \DateTime $end): int
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.duree) as total')
            ->where('t.utilisateur = :userId')
            ->andWhere('t.dateDebut >= :start')
            ->andWhere('t.dateDebut <= :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Trouve l'entrée en cours (non terminée) pour un utilisateur
     */
    public function findActiveEntry($userId): ?TimeEntry
    {
        return $this->createQueryBuilder('t')
            ->where('t.utilisateur = :userId')
            ->andWhere('t.dateFin IS NULL')
            ->setParameter('userId', $userId)
            ->orderBy('t.dateDebut', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
