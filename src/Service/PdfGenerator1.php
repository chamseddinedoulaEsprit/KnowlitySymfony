<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class PdfGenerator1
{
    private $twig;
    private $parameterBag;

    public function __construct(Environment $twig, ParameterBagInterface $parameterBag)
    {
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
    }

    public function generateQuizScorePdf($user, $cours, $userQuizSelection): string
    {
        // Configuration des options de Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
    
        // Calcul du score total et des réponses
        $totalScore = 0;
        $userResponses = $userQuizSelection->getResponse();
        $quizDetails = [];
        
        foreach ($userResponses as $response) {
            // Add each response's score
            $quizDetails[] = [
                'question' => $response->getQuestion()->getText(),
                'user_answer' => $response->getText(),
                'score' => $response->getScore(),
                'total_points' => $response->getQuestion()->getPoints(),
            ];
            $totalScore += $response->getScore();
        }

        // Calcul du score total du quiz
        $maxScore = $cours->getTotalPoints(); // Assuming the course has a total points property

        // Générez le HTML pour le PDF
        $html = $this->twig->render('pdf/quiz_score_confirmation.html.twig', [
            'user' => $user,
            'cours' => $cours,
            'app_name' => $this->parameterBag->get('app_name'),
            'quizDetails' => $quizDetails,
            'totalScore' => $totalScore,
            'maxScore' => $maxScore,
        ]);
    
        // Créez le PDF avec Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        return $dompdf->output();
    }
}
