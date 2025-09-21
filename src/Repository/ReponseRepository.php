<?php

namespace App\Repository;

use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    /**
     * Trouver des réponses en fonction du texte.
     *
     * @param string $text
     * @return Reponse[] Retourne un tableau d'objets Reponse.
     */
    public function findByText(string $text): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.text LIKE :text')  // Utilisation de LIKE pour une correspondance partielle
            ->setParameter('text', '%' . $text . '%')  // On cherche tous les textes qui contiennent la chaîne recherchée
            ->orderBy('r.id', 'ASC')  // Optionnel : Ordonner par id
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver toutes les réponses d'une question spécifique.
     *
     * @param int $questionId
     * @return Reponse[] Retourne un tableau d'objets Reponse.
     */
    public function findByQuestionId(int $questionId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.question = :questionId')  // Chercher toutes les réponses associées à une question spécifique
            ->setParameter('questionId', $questionId)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver des réponses en fonction de leur statut correct ou incorrect.
     *
     * @param bool $isCorrect
     * @return Reponse[] Retourne un tableau d'objets Reponse.
     */
    public function findByCorrectStatus(bool $isCorrect): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.is_correct = :isCorrect')  // Chercher toutes les réponses selon le statut correct
            ->setParameter('isCorrect', $isCorrect)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Vous pouvez ajouter d'autres méthodes personnalisées pour effectuer des recherches spécifiques.
// src/Repository/EvaluationRepository.php



}
