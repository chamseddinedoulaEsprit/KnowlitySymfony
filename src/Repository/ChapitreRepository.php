<?php

namespace App\Repository;

use App\Entity\Chapitre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chapitre>
 */
class ChapitreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapitre::class);
    }

    //    /**
    //     * @return Chapitre[] Returns an array of Chapitre objects
    //     */
        public function findByExampleField($value): array
        {
            return $this->createQueryBuilder('c')
                ->andWhere('c.cours = :val')
                ->setParameter('val', $value)
                ->orderBy('c.chapOrder', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findOneBySomeField($value): ?Chapitre
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
