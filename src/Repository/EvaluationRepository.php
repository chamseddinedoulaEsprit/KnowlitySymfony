<?php

namespace App\Repository;

use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evaluation>
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }
    public function findQuestionsOrderedByOrderQuestion(Evaluation $evaluation)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('q')
            ->from(Question::class, 'q')
            ->where('q.evaluation = :evaluation')
            ->setParameter('evaluation', $evaluation)
            ->orderBy('q.orderQuestion', 'ASC')  // Trie par 'orderQuestion'
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Evaluation[] Returns an array of Evaluation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evaluation
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/QuestionRepository.php

public function findFirstQuestionByEvaluation(Evaluation $evaluation)
{
    return $this->createQueryBuilder('q')
        ->where('q.evaluation = :evaluation')
        ->setParameter('evaluation', $evaluation)
        ->orderBy('q.orderQuestion', 'ASC') // Tri par orderQuestion
        ->setMaxResults(1) // Limiter à une seule question
        ->getQuery()
        ->getOneOrNullResult(); // Retourner la première question ou null si aucune
}

}
