<?php

namespace App\Repository;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use Doctrine\ORM\EntityManagerInterface;
/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function verification($email, $pass, EntityManagerInterface $em)
    {
        $request = $em->createQuery('SELECT s FROM App\Entity\User s WHERE s.email = :email AND s.confirm_password = :pass');
        $request->setParameter('email', $email);
        $request->setParameter('pass', $pass);
    
        $result = $request->getResult();
    
      
        return !empty($result);
    }

    public function findActiveUsers(int $page =1 ,int $limit = 1): array
    {
        $limit=abs($limit);
        $result= [] ;
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->andWhere('u.roles = :etudiant')
            ->setParameter('etudiant', "Etudiant") 
            ->setParameter('deleted', 0)
            ->setMaxResults($limit)
            ->setFirstResult(($page*$limit)-$limit);
            $paginator = new Paginator($query);
            $data = $paginator->getQuery()->getResult();
             if (empty($data)){
                return $query->getQuery()->getResult();  
             }
        $pages = ceil($paginator->count() /$limit);
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page']= $page;
        $result['limit'] =$limit;

            return $result;
    }


    public function findActiveUsersEnseignant(int $page =1 ,int $limit = 1): array
    {
        $limit=abs($limit);
        $result= [] ;
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->andWhere('u.roles = :etudiant')
            ->setParameter('etudiant', "Enseignant") 
            ->setParameter('deleted', 0)
            ->setMaxResults($limit)
            ->setFirstResult(($page*$limit)-$limit);
            $paginator = new Paginator($query);
            $data = $paginator->getQuery()->getResult();
             if (empty($data)){
                return $query->getQuery()->getResult();  
             }
        $pages = ceil($paginator->count() /$limit);
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page']= $page;
        $result['limit'] =$limit;

            return $result;
    }

    public function findActiveUsersAdmin(int $page =1 ,int $limit = 1): array
    {
        $limit=abs($limit);
        $result= [] ;
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->andWhere('u.roles = :etudiant')
            ->setParameter('etudiant', "Admin") 
            ->setParameter('deleted', 0)
            ->setMaxResults($limit)
            ->setFirstResult(($page*$limit)-$limit);
            $paginator = new Paginator($query);
            $data = $paginator->getQuery()->getResult();
             if (empty($data)){
                return $query->getQuery()->getResult();  
             }
        $pages = ceil($paginator->count() /$limit);
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page']= $page;
        $result['limit'] =$limit;

            return $result;
    }






    public function showall(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', 0)
            ->getQuery()
            ->getResult();
    }

    public function findEnseignant(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->andWhere('u.roles LIKE :enseignant')
            ->setParameter('deleted', 0)
            ->setParameter('enseignant', '%Enseignant%')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findInactiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', 1)
            ->getQuery()
            ->getResult();
            
    }
    public function generateVerificationCode()
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, 6); 
    }

    public function findOneById($value): ?User
        {
            return $this->createQueryBuilder('u')
                ->andWhere('u.id = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

