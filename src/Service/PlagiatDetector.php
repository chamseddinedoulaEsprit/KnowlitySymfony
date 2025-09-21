<?php

namespace App\Service;

use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;

class PlagiatDetector
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function analyzeResponse(Reponse $reponse): void
    {
        $suspicious = false;
        $details = [];

        // 1. Vérification du temps de réponse
        $timeSpent = $reponse->getSubmitTime()->getTimestamp() - $reponse->getStartTime()->getTimestamp();
        if ($timeSpent < 30 && strlen($reponse->getContenu()) > 200) {
            $suspicious = true;
            $details[] = "Temps de réponse suspicieusement court";
        }

        // 2. Vérification des réponses similaires
        $similarResponses = $this->findSimilarResponses($reponse);
        if (count($similarResponses) > 0) {
            $suspicious = true;
            $details[] = "Contenu similaire trouvé dans d'autres réponses";
        }

        // 3. Vérification du modèle de frappe
        if ($this->isTypingPatternSuspicious($reponse)) {
            $suspicious = true;
            $details[] = "Modèle de frappe suspect (copier-coller potentiel)";
        }

        // Mise à jour de l'entité Reponse
        $reponse->setPlagiatSuspect($suspicious);
        $reponse->setPlagiatDetails(implode("\n", $details));

        $this->entityManager->persist($reponse);
        $this->entityManager->flush();
    }

    private function findSimilarResponses(Reponse $reponse): array
    {
        $question = $reponse->getQuestion();
        $evaluation = $question->getEvaluation();
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r')
           ->from(Reponse::class, 'r')
           ->join('r.question', 'q')
           ->where('q.evaluation = :evaluation')
           ->andWhere('r.id != :currentResponse')
           ->andWhere('r.contenu LIKE :content')
           ->setParameter('evaluation', $evaluation)
           ->setParameter('currentResponse', $reponse->getId())
           ->setParameter('content', '%' . substr($reponse->getContenu(), 0, 100) . '%');

        return $qb->getQuery()->getResult();
    }

    private function isTypingPatternSuspicious(Reponse $reponse): bool
    {
        $pattern = $reponse->getTypingPattern();
        if (!$pattern) {
            return false;
        }

        // Vérification des modèles de frappe suspects
        $pasteCount = $pattern['pasteCount'] ?? 0;
        $keyPressCount = $pattern['keyPressCount'] ?? 0;

        // Si plus de 50% du contenu provient de copier-coller
        return $pasteCount > 0 && ($keyPressCount / strlen($reponse->getContenu())) < 0.5;
    }
}