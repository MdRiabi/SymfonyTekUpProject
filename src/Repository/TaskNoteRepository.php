<?php

namespace App\Repository;

use App\Entity\TaskNote;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskNote>
 */
class TaskNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskNote::class);
    }

    /**
     * Find all notes for a specific task, ordered by creation date (newest first)
     */
    public function findByTask(Tache $task): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.tache = :task')
            ->setParameter('task', $task)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent notes by a specific user
     */
    public function findRecentByUser(Utilisateur $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.auteur = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count notes for a specific task
     */
    public function countByTask(Tache $task): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.tache = :task')
            ->setParameter('task', $task)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
