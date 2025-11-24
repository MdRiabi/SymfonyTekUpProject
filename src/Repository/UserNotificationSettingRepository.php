<?php

namespace App\Repository;

use App\Entity\UserNotificationSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserNotificationSetting>
 *
 * @method UserNotificationSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserNotificationSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserNotificationSetting[]    findAll()
 * @method UserNotificationSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserNotificationSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotificationSetting::class);
    }

    public function save(UserNotificationSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserNotificationSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
