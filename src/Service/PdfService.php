<?php

namespace App\Service;

use App\Entity\Evaluation;
use App\Entity\User;
use App\Repository\ReponseRepository;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfService
{
    private $pdf;
    private $twig;
    private $reponseRepository;

    public function __construct(
        Pdf $pdf,
        Environment $twig,
        ReponseRepository $reponseRepository
    ) {
        $this->pdf = $pdf;
        $this->twig = $twig;
        $this->reponseRepository = $reponseRepository;
    }

    public function generateEvaluationPdf(Evaluation $evaluation, User $student): Response
    {
        // Récupérer les réponses de l'étudiant
        $responses = $this->reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $student
        ]);

        // Calculer le score total et maximal
        $totalScore = 0;
        $maxScore = 0;
        $hasProfanity = false;
        foreach ($responses as $response) {
            if ($response->getNote() !== null) {
                $totalScore += $response->getNote();
            }
            $maxScore += $response->getQuestion()->getPoint();
            
            // Vérifier si une réponse contient un avertissement pour contenu inapproprié
            if ($response->getCommentaire() && str_starts_with($response->getCommentaire(), 'AVERTISSEMENT')) {
                $hasProfanity = true;
            }
        }

        // Générer le HTML
        $html = $this->twig->render('evaluation/pdf_template.html.twig', [
            'evaluation' => $evaluation,
            'student' => $student,
            'responses' => $responses,
            'totalScore' => $totalScore,
            'maxScore' => $maxScore,
            'hasProfanity' => $hasProfanity,
            'badgeThreshold' => 70 // Seuil pour obtenir un badge
        ]);

        // Générer le PDF
        $pdf = $this->pdf->getOutputFromHtml($html);

        // Créer un nom de fichier descriptif
        $filename = sprintf(
            'evaluation_%s_%s_%s.pdf',
            $evaluation->getId(),
            $student->getId(),
            (new \DateTime())->format('Y-m-d')
        );

        // Retourner la réponse
        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ]
        );
    }
}