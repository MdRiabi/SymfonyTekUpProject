<?php

namespace App\Repository;

use App\Entity\TaskComment;
use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskComment>
 */
class TaskCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskComment::class);
    }

    /**
     * Find all comments for a specific task, ordered by creation date (oldest first)
     */
    public function findByTask(Tache $task): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.tache = :task')
            ->setParameter('task', $task)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count comments for a specific task
     */
    public function countByTask(Tache $task): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.tache = :task')
            ->setParameter('task', $task)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
