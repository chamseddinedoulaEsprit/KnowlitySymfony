<?php

namespace App\Service;

use App\Entity\Evaluation;
use App\Entity\Resultat;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculateStatistics(Evaluation $evaluation): array
    {
        $results = $evaluation->getResultats();
        if ($results->isEmpty()) {
            return [
                'moyenne' => 0,
                'mediane' => 0,
                'ecart_type' => 0,
                'min' => 0,
                'max' => 0,
                'nombre_participants' => 0,
                'taux_reussite' => 0,
            ];
        }

        $scores = [];
        foreach ($results as $result) {
            $scores[] = $result->getScore();
        }

        $nombre_participants = count($scores);
        $moyenne = array_sum($scores) / $nombre_participants;
        
        // Calcul de la médiane
        sort($scores);
        $middle = floor(($nombre_participants - 1) / 2);
        if ($nombre_participants % 2) {
            $mediane = $scores[$middle];
        } else {
            $mediane = ($scores[$middle] + $scores[$middle + 1]) / 2;
        }

        // Calcul de l'écart-type
        $variance = 0;
        foreach ($scores as $score) {
            $variance += pow($score - $moyenne, 2);
        }
        $ecart_type = sqrt($variance / $nombre_participants);

        // Calcul du taux de réussite (score > 50% du max_score)
        $seuil_reussite = $evaluation->getMaxScore() * 0.5;
        $reussites = array_filter($scores, function($score) use ($seuil_reussite) {
            return $score >= $seuil_reussite;
        });
        $taux_reussite = (count($reussites) / $nombre_participants) * 100;

        return [
            'moyenne' => round($moyenne, 2),
            'mediane' => round($mediane, 2),
            'ecart_type' => round($ecart_type, 2),
            'min' => min($scores),
            'max' => max($scores),
            'nombre_participants' => $nombre_participants,
            'taux_reussite' => round($taux_reussite, 2),
        ];
    }
}