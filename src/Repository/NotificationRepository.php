<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Trouve les notifications non lues pour un utilisateur
     */
    public function findUnreadByUser($userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :userId')
            ->andWhere('n.lu = :lu')
            ->setParameter('userId', $userId)
            ->setParameter('lu', false)
            ->orderBy('n.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les notifications pour un utilisateur
     */
    public function findByUser($userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
