<?php

namespace App\Repository;

use App\Entity\UserQuizSelection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserQuizSelection>
 */
class UserQuizSelectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserQuizSelection::class);
    }
    public function getScoreStatistics(): array
    {
        $scores = $this->createQueryBuilder('u')
            ->select('u.score')
            ->getQuery()
            ->getResult();

        // Extract scores into a simple array
        $scores = array_column($scores, 'score');

        // Calculate statistics
        return [
            'max' => max($scores),
            'min' => min($scores),
            'average' => array_sum($scores) / count($scores),
        ];
    }

    //    /**
    //     * @return UserQuizSelection[] Returns an array of UserQuizSelection objects
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

    //    public function findOneBySomeField($value): ?UserQuizSelection
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
