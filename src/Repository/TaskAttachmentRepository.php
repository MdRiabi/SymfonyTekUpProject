<?php

namespace App\Repository;

use App\Entity\TaskAttachment;
use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskAttachment>
 */
class TaskAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskAttachment::class);
    }

    /**
     * Find all attachments for a specific task, ordered by upload date (newest first)
     */
    public function findByTask(Tache $task): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.tache = :task')
            ->setParameter('task', $task)
            ->orderBy('a.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count attachments for a specific task
     */
    public function countByTask(Tache $task): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.tache = :task')
            ->setParameter('task', $task)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total size of attachments for a task (in bytes)
     */
    public function getTotalSizeByTask(Tache $task): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('SUM(a.filesize)')
            ->where('a.tache = :task')
            ->setParameter('task', $task)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}
