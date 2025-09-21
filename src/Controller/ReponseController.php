<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\EvaluationRepository;

use App\Repository\ReponseRepository;
use App\Repository\QuestionRepository;
use App\Service\ProfanityCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route(name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/new/{questionId}', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, QuestionRepository $questionRepository, ProfanityCheckerService $profanityChecker, MailerInterface $mailer, $questionId): Response
    {
        // Récupérer la question par ID
        $question = $questionRepository->find($questionId);
    
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }
    
        // Créer une nouvelle réponse liée à la question
        $reponse = new Reponse();
        $reponse->setQuestion($question);
    
        // Récupérer l'ID utilisateur (remplacez cette ligne par l'authentification réelle plus tard)
        $userId = 1234567; // Remplacer par l'ID réel de l'utilisateur connecté
        $reponse->setUserId($userId);
    
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
    
        // Initialisation de la variable nextQuestionId
        $nextQuestionId = null;
    
        // Logique pour récupérer la question suivante
        $nextQuestion = $questionRepository->findOneBy([
            'ordreQuestion' => $question->getOrdreQuestion() + 1,
            'evaluation' => $question->getEvaluation()
        ]);
    
        if ($nextQuestion) {
            $nextQuestionId = $nextQuestion->getId(); // Assigner l'ID de la question suivante
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des mots inappropriés
            if ($profanityChecker->containsProfanity($reponse->getText())) {
                // Notifier l'enseignant par email
                $email = (new Email())
                    ->from('votre-app@domaine.com')
                    ->to('enseignant@email.com')  // À remplacer par l'email de l'enseignant
                    ->subject('Contenu inapproprié détecté')
                    ->html(sprintf(
                        'Un contenu inapproprié a été détecté dans une réponse:<br>
                        Question: %s<br>
                        Réponse: %s<br>
                        Étudiant ID: %s',
                        $question->getText(),
                        $reponse->getText(),
                        $userId
                    ));

                try {
                    $mailer->send($email);
                } catch (\Exception $e) {
                    // Log l'erreur silencieusement
                }

                // Marquer la réponse comme suspecte
                $reponse->setPlagiatSuspect(true);
                $reponse->setPlagiatDetails('Contenu inapproprié détecté');
            }

            // Persist la réponse
            $entityManager->persist($reponse);
            $entityManager->flush();
    
            // Vérification de la question suivante
            if ($reponse->getQuestion()) {
                $evaluation = $reponse->getQuestion()->getEvaluation();
                $questions = $evaluation->getQuestions();
                $maxOrder = null;
    
                foreach ($questions as $question) {
                    if ($maxOrder === null || $question->getOrdreQuestion() > $maxOrder) {
                        $maxOrder = $question->getOrdreQuestion();
                    }
                }
    
                // Si c'est la dernière question, rediriger vers la page d'évaluation
                if ($reponse->getQuestion()->getOrdreQuestion() === $maxOrder) {
                    return $this->redirectToRoute('etudiant_evaluation_index', [], Response::HTTP_SEE_OTHER);
                } else {
                    // Sinon, passer à la question suivante
                    $nextQuestion = $questionRepository->findOneBy([
                        'ordreQuestion' => $reponse->getQuestion()->getOrdreQuestion() + 1,
                        'evaluation' => $evaluation
                    ]);
    
                    if ($nextQuestion) {
                        // Rediriger vers la question suivante après soumission du formulaire
                        return $this->redirectToRoute('app_reponse_new', ['questionId' => $nextQuestion->getId()], Response::HTTP_SEE_OTHER);
                    }
                }
            }
        }
    
        // Passer la question et nextQuestionId à la vue
        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
            'question' => $question->getText(),
            'nextQuestionId' => $nextQuestionId,
        ]);
    }
        #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/evaluation/{id}/etudiants', name: 'app_reponse_etudiants', methods: ['GET'])]
    public function showStudentsByEvaluation(int $id, ReponseRepository $reponseRepository, EvaluationRepository $evaluationRepository): Response
    {
        $evaluation = $evaluationRepository->find($id);
    
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }
    
        // Récupérer les étudiants ayant répondu à cette évaluation
        $etudiants = $reponseRepository->createQueryBuilder('r')
            ->select('r.userId') // Sélectionner userId
            ->innerJoin('r.question', 'q')
            ->where('q.evaluation = :evaluationId')
            ->setParameter('evaluationId', $id)
            ->groupBy('r.userId')
            ->getQuery()
            ->getResult();
    
        return $this->render('reponse/etudiants.html.twig', [
            'evaluation' => $evaluation,
            'etudiants' => $etudiants,
        ]);
    }
    


#[Route('/evaluation/{evaluationId}/user/{userId}', name: 'app_reponse_user', methods: ['GET'])]
public function showUserResponsesByEvaluation(int $evaluationId, int $userId, ReponseRepository $reponseRepository, EvaluationRepository $evaluationRepository): Response
{
    $evaluation = $evaluationRepository->find($evaluationId);

    if (!$evaluation) {
        throw $this->createNotFoundException('Évaluation non trouvée');
    }

    // Récupérer les réponses de cet étudiant pour cette évaluation
    $reponses = $reponseRepository->createQueryBuilder('r')
        ->innerJoin('r.question', 'q') // Jointure sur la question
        ->where('q.evaluation = :evaluationId') // Filtrage par évaluation
        ->andWhere('r.userId = :userId')  // Filtrage par userId (au lieu de r.user)
        ->setParameter('evaluationId', $evaluationId)
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();

    return $this->render('reponse/user_responses.html.twig', [
        'evaluation' => $evaluation,
        'reponses' => $reponses,
        'userId' => $userId,
    ]);
}


    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de la réponse correcte lors de l'édition
            if ($this->isAnswerCorrect($reponse, $reponse->getQuestion())) {
                $reponse->setIsCorrect(true); // Marquer la réponse comme correcte
            } else {
                $reponse->setIsCorrect(false); // Marquer la réponse comme incorrecte
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->get('token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }

    // Fonction pour vérifier si la réponse est correcte
    private function isAnswerCorrect(Reponse $reponse, $question): bool
    {
        // Logique pour comparer la réponse à une réponse correcte prédéfinie
        // Exemple : la réponse correcte pourrait être définie dans l'entité Question
        // ou être une valeur associée à la question. Voici un exemple simple :
        
        $correctAnswer = $question->getCorrectAnswer(); // Supposons que la question ait une réponse correcte
        if (!$correctAnswer) {
            // Gestion de cas où il n'y a pas de réponse correcte définie pour la question
            return false;
        }

        // Comparer la réponse soumise avec la réponse correcte
        return strtolower($reponse->getText()) === strtolower($correctAnswer);
    }
}