<?php

namespace App\Repository;

use App\Entity\ProjectHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectHistory::class);
    }

    public function findByProjet(int $projetId, int $limit = 50): array
    {
        return $this->createQueryBuilder('ph')
            ->where('ph.projet = :projetId')
            ->setParameter('projetId', $projetId)
            ->orderBy('ph.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
