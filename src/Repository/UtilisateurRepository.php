<?php  
  
namespace App\Repository;  
  
use App\Entity\Utilisateur;  
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;  
use Doctrine\Persistence\ManagerRegistry;  
  
class UtilisateurRepository extends ServiceEntityRepository  
{  
    public function __construct(ManagerRegistry $registry)  
    {  
        parent::__construct($registry, Utilisateur::class);  
    }  
    /**
     * @return Utilisateur[] Returns an array of Utilisateur objects
     */
    public function findByRole(string $roleName): array
    {
        // The DB stores roles as 'Admin', 'Client', etc.
        // The input is usually 'ROLE_ADMIN'.
        
        // 1. Remove 'ROLE_' prefix
        $roleNameClean = str_replace('ROLE_', '', $roleName);
        
        // 2. Convert to Title Case (e.g. 'ADMIN' -> 'Admin') because DB has 'Admin'
        $roleNameFormatted = ucfirst(strtolower($roleNameClean));
        
        return $this->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->andWhere('r.nomRole = :role')
            ->setParameter('role', $roleNameFormatted)
            ->getQuery()
            ->getResult();
    }
}  
