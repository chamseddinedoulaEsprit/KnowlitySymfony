<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\CoursType;
use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use App\Form\EvaluationType;
use App\Form\QuestionType;
use App\Repository\UserRepository;
use App\Repository\EvaluationRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Service\MailService;
use App\Service\StatisticsService;
use App\Service\PdfService;
use App\Service\ProfanityCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

#[Route('/evaluation')]
final class EvaluationController extends AbstractController
{
    private StatisticsService $statisticsService;
    private MailService $mailService;
    private PdfService $pdfService;

    public function __construct(StatisticsService $statisticsService, MailService $mailService, PdfService $pdfService)
    {
        $this->statisticsService = $statisticsService;
        $this->mailService = $mailService;
        $this->pdfService = $pdfService;
    }

    // Routes pour l'enseignant
    #[Route('/enseignant', name: 'app_evaluation_indexenseignant', methods: ['GET'])]
    public function indexEnseignant(Request $request, EvaluationRepository $evaluationRepository): Response
    {
        // Récupérer l'ID du cours depuis la requête
        $coursId = $request->query->get('coursId');
        
        if (!$coursId) {
            // Rediriger vers la page des cours si aucun coursId n'est fourni
            return $this->redirectToRoute('app_cours_index');
        }
        
        $evaluations = $evaluationRepository->findBy(['cours' => $coursId]);
        
        return $this->render('evaluation/indexenseignant.html.twig', [
            'evaluations' => $evaluations,
            'coursId' => $coursId
        ]);
    }

    #[Route('/enseignant/new/{coursId}', name: 'app_evaluation_newenseignant', methods: ['GET', 'POST'])]
    public function newEnseignant(int $coursId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la session et l'ID de l'utilisateur connecté
        $session = $request->getSession();
        $userId = $session->get('id');

        // Récupérer l'utilisateur et le cours associé
        $user = $entityManager->getRepository(User::class)->find($userId);
        $cour = $entityManager->getRepository(Cours::class)->find($coursId);

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour créer une évaluation.");
        }

        // Vérifier si le cours existe
        if (!$cour) {
            throw $this->createNotFoundException("Le cours spécifié n'existe pas.");
        }

        // Créer une nouvelle évaluation
        $evaluation = new Evaluation();
        $evaluation->setCours($cour); // Associer le cours à l'évaluation
        $evaluation->setCreateAt(new \DateTime()); // Définir la date de création

        // Créer et gérer le formulaire
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evaluation);
            $entityManager->flush();

            // Envoyer l'email de confirmation à l'enseignant
            $this->mailService->sendEvaluationCreatedToTeacher($user, $evaluation);

            // Rediriger vers la création de questions pour cette évaluation
            return $this->redirectToRoute('app_question_new', ['evaluationId' => $evaluation->getId()], Response::HTTP_SEE_OTHER);
        }

        // Afficher le formulaire de création d'évaluation
        return $this->render('evaluation/new.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form->createView(),
            'coursId' => $coursId
        ]);
    }

    #[Route('/enseignant/{id}', name: 'app_evaluation_showenseignant', methods: ['GET'])]
    public function showEnseignant(Evaluation $evaluation, QuestionRepository $questionRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les questions de l'évaluation
        $questions = $questionRepository->findBy(['evaluation' => $evaluation]);
        
        return $this->render('evaluation/showenseignant.html.twig', [
            'evaluation' => $evaluation,
            'questions' => $questions
        ]);
    }

    #[Route('/enseignant/{id}/edit', name: 'app_evaluation_editenseignant', methods: ['GET', 'POST'])]
    public function editEnseignant(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Évaluation modifiée avec succès !');
            return $this->redirectToRoute('app_evaluation_showenseignant', ['id' => $evaluation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluation/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/enseignantremove/{id}', name: 'app_evaluation_showenseignantremove', methods: ['GET', 'POST'])]
    public function showEnseignantRemove(
        Request $request, 
        Evaluation $evaluation, 
        QuestionRepository $questionRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les questions associées à l'évaluation
        $questions = $questionRepository->findBy(['evaluation' => $evaluation]);
    
        // Récupérer les réponses des étudiants par question
        $responsesByQuestion = [];
        foreach ($questions as $question) {
            $responsesByQuestion[$question->getId()] = [
                'question' => $question,
                'responses' => []
            ];
        }
    
        foreach ($questions as $question) {
            $responses = $entityManager->getRepository(Reponse::class)
                ->findBy(['question' => $question]);
            $responsesByQuestion[$question->getId()]['responses'] = $responses;
        }
    
        // Création d'une nouvelle question via un formulaire
        $newQuestion = new Question();
        $newQuestion->setEvaluation($evaluation);
        $form = $this->createForm(QuestionType::class, $newQuestion);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Associer un ordre de question unique
            $lastQuestion = $entityManager->getRepository(Question::class)
                ->findOneBy(['evaluation' => $evaluation], ['ordreQuestion' => 'DESC']);
            $newOrder = $lastQuestion ? $lastQuestion->getOrdreQuestion() + 1 : 1;
            $newQuestion->setOrdreQuestion($newOrder);
    
            // Sauvegarder la nouvelle question
            $entityManager->persist($newQuestion);
            $entityManager->flush();
    
            $this->addFlash('success', 'La question a été ajoutée avec succès.');
    
            return $this->redirectToRoute('app_evaluation_showenseignant', ['id' => $evaluation->getId()]);
        }
    
        return $this->render('evaluation/showenseignant.html.twig', [
            'evaluation' => $evaluation,
            'questions' => $questions,
            'responsesByQuestion' => $responsesByQuestion,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/enseignant/{id}', name: 'app_evaluation_deleteenseignant', methods: ['POST'])]
    public function deleteEnseignant(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evaluation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
            $this->addFlash('success', 'L\'évaluation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_evaluation_indexenseignant', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/evaluation/correct/{id}', name: 'app_evaluation_correct', methods: ['GET'])]
    public function correct(
        Evaluation $evaluation,
        QuestionRepository $questionRepository,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les questions et les réponses
        $questions = $questionRepository->findBy(['evaluation' => $evaluation], ['ordreQuestion' => 'ASC']);
        $responses = $reponseRepository->findBy(['evaluation' => $evaluation]);

        // Organiser les réponses par étudiant
        $studentResponses = [];
        foreach ($responses as $response) {
            $studentId = $response->getUser()->getId();
            if (!isset($studentResponses[$studentId])) {
                $studentResponses[$studentId] = [
                    'student' => $response->getUser(),
                    'responses' => []
                ];
            }
            $studentResponses[$studentId]['responses'][] = $response;
        }

        return $this->render('evaluation/correct.html.twig', [
            'evaluation' => $evaluation,
            'questions' => $questions,
            'studentResponses' => $studentResponses
        ]);
    }

    #[Route('/enseignant/{evaluationId}/students', name: 'app_evaluation_student_list', methods: ['GET'])]
    public function studentList(
        int $evaluationId,
        EvaluationRepository $evaluationRepository,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $evaluation = $evaluationRepository->find($evaluationId);
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        // Récupérer toutes les questions de l'évaluation
        $questions = $evaluation->getQuestions();
        $totalQuestions = count($questions);

        // Calculer le score maximum total
        $scoreMaxTotal = 0;
        foreach ($questions as $question) {
            $scoreMaxTotal += $question->getPoint();
        }

        // Récupérer toutes les réponses pour cette évaluation
        $responses = $reponseRepository->findBy(['evaluation' => $evaluation]);
        
        // Organiser les réponses par étudiant
        $studentData = [];
        foreach ($responses as $response) {
            if (!$response->getUser()) {
                continue; // Ignorer les réponses sans utilisateur
            }
            
            $studentId = $response->getUser()->getId();
            if (!isset($studentData[$studentId])) {
                $studentData[$studentId] = [
                    'student' => $response->getUser(),
                    'totalScore' => 0,
                    'correctedCount' => 0,
                    'submittedCount' => 0,
                    'totalQuestions' => $totalQuestions,
                    'lastSubmitTime' => $response->getSubmitTime(),
                    'isFullyCorrected' => false
                ];
            }

            // Compter les réponses soumises
            $studentData[$studentId]['submittedCount']++;

            // Si la réponse a une note et est marquée comme corrigée
            if ($response->getNote() !== null && $response->getStatus() === 'corrige') {
                $studentData[$studentId]['totalScore'] += $response->getNote();
                $studentData[$studentId]['correctedCount']++;
            }

            // Mettre à jour la dernière date de soumission si plus récente
            if ($response->getSubmitTime() > $studentData[$studentId]['lastSubmitTime']) {
                $studentData[$studentId]['lastSubmitTime'] = $response->getSubmitTime();
            }
        }

        // Calculer la moyenne et marquer les copies entièrement corrigées
        $totalScore = 0;
        $correctedCopies = 0;
        foreach ($studentData as &$data) {
            // Une copie est considérée comme corrigée si toutes les réponses soumises sont corrigées
            if ($data['correctedCount'] == $data['submittedCount']) {
                $data['isFullyCorrected'] = true;
                $totalScore += $data['totalScore'];
                $correctedCopies++;
            }
        }

        // Calculer la moyenne de la classe
        $moyenne = $correctedCopies > 0 ? ($totalScore / $correctedCopies) : 0;

        return $this->render('evaluation/student_list.html.twig', [
            'evaluation' => $evaluation,
            'studentData' => $studentData,
            'scoreMaxTotal' => $scoreMaxTotal,
            'correctedCopies' => $correctedCopies,
            'moyenne' => $moyenne
        ]);
    }

    #[Route('/evaluation/enseignant/{evaluationId}/student/{studentId}/correct', name: 'app_evaluation_correct_student', methods: ['GET', 'POST'])]
    public function correctStudentEnseignant(
        int $evaluationId,
        int $studentId,
        EvaluationRepository $evaluationRepository,
        UserRepository $userRepository,
        ReponseRepository $reponseRepository,
        QuestionRepository $questionRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $evaluation = $evaluationRepository->find($evaluationId);
        $student = $userRepository->find($studentId);

        if (!$evaluation || !$student) {
            throw $this->createNotFoundException('Évaluation ou étudiant non trouvé.');
        }

        // Traitement du formulaire de correction
        if ($request->isMethod('POST')) {
            try {
                $data = $request->request->all();
                
                if (isset($data['notes'])) {
                    foreach ($data['notes'] as $questionId => $note) {
                        $reponse = $reponseRepository->findOneBy([
                            'evaluation' => $evaluation,
                            'user' => $student,
                            'question' => $questionId
                        ]);

                        if ($reponse) {
                            $reponse->setNote((int)$note);
                            $reponse->setCommentaire($data['commentaires'][$questionId] ?? null);
                            $reponse->setStatus('corrige');
                            $reponse->setCorrectedAt(new \DateTime());
                        }
                    }

                    $entityManager->flush();
                    $this->addFlash('success', 'Les corrections ont été enregistrées avec succès.');
                    return $this->redirectToRoute('app_evaluation_student_list', ['evaluationId' => $evaluationId]);
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement des corrections.');
                throw $e;
            }
        }

        // Récupérer les questions et les réponses
        $questions = $questionRepository->findBy(['evaluation' => $evaluation], ['ordreQuestion' => 'ASC']);
        $responses = $reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $student
        ]);

        // Calculer le score maximum total
        $scoreMaxTotal = 0;
        foreach ($questions as $question) {
            $scoreMaxTotal += $question->getPoint();
        }

        // Organiser les réponses par question
        $questionResponses = [];
        foreach ($questions as $question) {
            $response = null;
            foreach ($responses as $r) {
                if ($r->getQuestion()->getId() === $question->getId()) {
                    $response = $r;
                    break;
                }
            }

            if ($response) {
                $questionResponses[] = [
                    'question' => $question,
                    'reponse' => $response,
                    'isCorrected' => $response->getNote() !== null
                ];
            }
        }

        return $this->render('evaluation/correct_list.html.twig', [
            'evaluation' => $evaluation,
            'student' => $student,
            'questionResponses' => $questionResponses,
            'scoreMaxTotal' => $scoreMaxTotal
        ]);
    }

    #[Route('/enseignant/evaluation/{evaluationId}/save-correction/{studentId}', name: 'evaluation_save_correctionenseignant', methods: ['POST'])]
    public function saveCorrectionEnseignant(
        Request $request,
        int $evaluationId,
        int $studentId,
        EntityManagerInterface $entityManager,
        ReponseRepository $reponseRepository,
        EvaluationRepository $evaluationRepository,
        UserRepository $userRepository
    ): Response {
        try {
            $evaluation = $evaluationRepository->find($evaluationId);
            if (!$evaluation) {
                throw $this->createNotFoundException('Évaluation non trouvée');
            }

            $student = $userRepository->find($studentId);
            if (!$student) {
                throw $this->createNotFoundException('Étudiant non trouvé');
            }

            $data = $request->request->all();
            $notes = $data['notes'] ?? [];
            $commentaires = $data['commentaires'] ?? [];
            $confirmInappropriate = $data['confirm_inappropriate'] ?? [];
            $warningMessages = $data['warning_messages'] ?? [];

            // Récupérer toutes les réponses de l'étudiant pour cette évaluation
            $allResponses = $reponseRepository->findBy([
                'evaluation' => $evaluation,
                'user' => $student
            ]);

            if (empty($allResponses)) {
                throw new \Exception('Aucune réponse trouvée pour cet étudiant');
            }

            foreach ($allResponses as $reponse) {
                $reponseId = $reponse->getId();
                
                // Vérifier si la réponse contient du contenu inapproprié
                if ($reponse->isPlagiatSuspect()) {
                    // Si l'enseignant confirme le contenu inapproprié
                    if (isset($confirmInappropriate[$reponseId])) {
                        $warningMessage = $warningMessages[$reponseId] ?? 'Contenu inapproprié détecté dans votre réponse. Cette violation entraîne une note de 0.';
                        $reponse->setNote(0);
                        $reponse->setCommentaire("AVERTISSEMENT: " . $warningMessage);
                        $reponse->setStatus('corrige');
                        $reponse->setCorrectedAt(new \DateTime());
                    }
                    // Si l'enseignant ne confirme pas, traiter comme une réponse normale
                    elseif (isset($notes[$reponseId])) {
                        $reponse->setNote((float)$notes[$reponseId]);
                        $reponse->setCommentaire($commentaires[$reponseId] ?? '');
                        $reponse->setStatus('corrige');
                        $reponse->setCorrectedAt(new \DateTime());
                    }
                }
                // Correction normale pour les réponses sans suspicion
                elseif (isset($notes[$reponseId])) {
                    $reponse->setNote((float)$notes[$reponseId]);
                    $reponse->setCommentaire($commentaires[$reponseId] ?? '');
                    $reponse->setStatus('corrige');
                    $reponse->setCorrectedAt(new \DateTime());
                }
                
                $entityManager->persist($reponse);
            }

            $entityManager->flush();
            
            // Vérifier les résultats de la correction
            $nonCorrigeCount = 0;
            $inappropriateCount = 0;
            foreach ($allResponses as $reponse) {
                if ($reponse->getNote() === null || $reponse->getStatus() !== 'corrige') {
                    $nonCorrigeCount++;
                }
                if (isset($confirmInappropriate[$reponse->getId()])) {
                    $inappropriateCount++;
                }
            }

            if ($inappropriateCount > 0) {
                $this->addFlash('warning', 'Des contenus inappropriés ont été détectés et sanctionnés.');
            }
            if ($nonCorrigeCount === 0) {
                $this->addFlash('success', 'Toutes les réponses ont été corrigées avec succès.');
            } else {
                $this->addFlash('info', 'Certaines réponses n\'ont pas été corrigées.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la correction : ' . $e->getMessage());
            throw $e;
        }

        return $this->redirectToRoute('app_evaluation_student_list', ['evaluationId' => $evaluationId]);
    }

    #[Route('/evaluation/enseignant/{id}/statistics', name: 'app_evaluation_statistics', methods: ['GET'])]
    public function showStatistics(
        Evaluation $evaluation, 
        ReponseRepository $reponseRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les paramètres de tri et recherche
        $sortBy = $request->query->get('sort', 'score');
        $order = $request->query->get('order', 'DESC');
        $search = $request->query->get('search', '');

        // Construire la requête DQL de base
        $dql = 'SELECT DISTINCT u.id, u.nom, u.prenom, u.email, 
                (SELECT COUNT(r2.id) FROM App\Entity\Reponse r2 WHERE r2.user = u AND r2.evaluation = :evaluation) as totalAnswers,
                (SELECT COALESCE(SUM(r3.note), 0) FROM App\Entity\Reponse r3 WHERE r3.user = u AND r3.evaluation = :evaluation) as totalScore,
                (SELECT COUNT(r4.id) FROM App\Entity\Reponse r4 WHERE r4.user = u AND r4.evaluation = :evaluation AND r4.note IS NOT NULL) as gradedAnswers
                FROM App\Entity\User u
                JOIN App\Entity\Reponse r WITH r.user = u
                WHERE r.evaluation = :evaluation';

        // Ajouter la condition de recherche si nécessaire
        if ($search) {
            $dql .= ' AND (LOWER(u.nom) LIKE LOWER(:search) OR LOWER(u.prenom) LIKE LOWER(:search) OR LOWER(u.email) LIKE LOWER(:search))';
        }

        // Ajouter l'ordre de tri
        $dql .= ' ORDER BY ' . ($sortBy === 'name' ? 'u.nom' : 'totalScore') . ' ' . $order;

        // Créer et exécuter la requête
        $query = $entityManager->createQuery($dql)
            ->setParameter('evaluation', $evaluation);

        if ($search) {
            $query->setParameter('search', '%' . $search . '%');
        }

        $studentRankings = $query->getResult();

        // Calculer les statistiques globales
        $totalStudents = count($studentRankings);
        $totalQuestions = count($evaluation->getQuestions());
        $scoreMaxTotal = array_sum(array_map(fn($q) => $q->getPoint(), $evaluation->getQuestions()->toArray()));
        
        // Calculer les statistiques détaillées
        $totalScores = array_column($studentRankings, 'totalScore');
        $averageScore = $totalStudents > 0 ? array_sum($totalScores) / $totalStudents : 0;
        
        // Calculer le taux de réussite (pourcentage d'étudiants ayant plus de 50%)
        $passCount = array_reduce($totalScores, function($count, $score) use ($scoreMaxTotal) {
            return $count + ($score >= $scoreMaxTotal * 0.5 ? 1 : 0);
        }, 0);
        $passRate = $totalStudents > 0 ? ($passCount / $totalStudents) * 100 : 0;

        // Préparer les données pour la distribution des scores
        $scoreRanges = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0
        ];

        foreach ($totalScores as $score) {
            $percentage = ($score / $scoreMaxTotal) * 100;
            if ($percentage <= 20) $scoreRanges['0-20%']++;
            elseif ($percentage <= 40) $scoreRanges['21-40%']++;
            elseif ($percentage <= 60) $scoreRanges['41-60%']++;
            elseif ($percentage <= 80) $scoreRanges['61-80%']++;
            else $scoreRanges['81-100%']++;
        }

        return $this->render('evaluation/statistics.html.twig', [
            'evaluation' => $evaluation,
            'totalStudents' => $totalStudents,
            'averageScore' => $averageScore,
            'scoreMaxTotal' => $scoreMaxTotal,
            'passRate' => $passRate,
            'scoreRanges' => $scoreRanges,
            'studentRankings' => $studentRankings,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentSearch' => $search,
            'totalQuestions' => $totalQuestions
        ]);
    }

    #[Route('/etudiant/{id}/resultat', name: 'app_evaluation_show_result')]
    public function showResult(
        Evaluation $evaluation,
        ReponseRepository $reponseRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer les réponses triées par l'ordre des questions
        $reponses = $reponseRepository->createQueryBuilder('r')
            ->leftJoin('r.question', 'q')
            ->where('r.user = :user')
            ->andWhere('r.evaluation = :evaluation')
            ->setParameter('user', $user)
            ->setParameter('evaluation', $evaluation)
            ->orderBy('q.ordreQuestion', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($reponses)) {
            $this->addFlash('warning', 'Vous n\'avez pas encore répondu à cette évaluation.');
            return $this->redirectToRoute('app_evaluation_indexetudiant');
        }

        // Organiser les réponses par question
        $reponsesOrganisees = [];
        $totalScore = 0;
        $maxScore = 0;
        $hasInappropriateContent = false;
        $submitTime = null;

        foreach ($reponses as $reponse) {
            $questionId = $reponse->getQuestion()->getId();
            $reponsesOrganisees[$questionId] = $reponse;
            
            if ($reponse->getSubmitTime() && (!$submitTime || $reponse->getSubmitTime() > $submitTime)) {
                $submitTime = $reponse->getSubmitTime();
            }

            if ($reponse->isPlagiatSuspect()) {
                $hasInappropriateContent = true;
                $reponse->setNote(0);
                $entityManager->persist($reponse);
            }
            
            $totalScore += $reponse->getNote();
            $maxScore += $reponse->getQuestion()->getPoint();
        }

        if ($hasInappropriateContent) {
            $totalScore = 0;
            $this->addFlash('danger', 'Contenu inapproprié détecté. Votre note a été mise à 0.');
            $entityManager->flush();
        }

        return $this->render('evaluation/show_result.html.twig', [
            'evaluation' => $evaluation,
            'reponses' => array_values($reponsesOrganisees), // Convertir en tableau indexé
            'totalScore' => $totalScore,
            'maxScore' => $maxScore,
            'hasInappropriateContent' => $hasInappropriateContent,
            'submitTime' => $submitTime
        ]);
    }

    #[Route('/etudiant/{id}/pdf', name: 'app_evaluation_pdf')]
    public function downloadPdf(
        Evaluation $evaluation,
        ReponseRepository $reponseRepository,
        UserRepository $userRepository,
        Request $request
    ): Response {
        // Récupérer l'ID de l'utilisateur depuis la session
        $session = $request->getSession();
        $userId = $session->get('id');
    
        if (!$userId) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }
    
        // Récupérer l'utilisateur depuis la base de données
        $user = $userRepository->find($userId);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        // Vérifier que l'utilisateur a des réponses pour cette évaluation
        $responses = $reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $user
        ]);
    
        if (empty($responses)) {
            throw $this->createNotFoundException('Aucune réponse trouvée pour cette évaluation.');
        }
    
        return $this->pdfService->generatePdf($evaluation, $user);
    }

    #[Route('/evaluation/export-pdf/{id}', name: 'evaluation_export_pdf', methods: ['GET'])]
    public function exportPdf(
        Evaluation $evaluation,
        ReponseRepository $reponseRepository,
        QuestionRepository $questionRepository,
        UserRepository $userRepository,
        PdfService $pdfService,
        Request $request
    ): Response {
        // Récupérer l'utilisateur connecté
        $session = $request->getSession();
        $userId = $session->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Vérifier si l'utilisateur a répondu à l'évaluation
        $responses = $reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $user
        ]);

        if (empty($responses)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas encore répondu à cette évaluation.');
        }

        // Vérifier si toutes les réponses sont corrigées
        $totalScore = 0;
        $maxScore = 0;
        $allCorrected = true;

        foreach ($responses as $response) {
            if ($response->getNote() === null) {
                $allCorrected = false;
                break;
            }
            $totalScore += $response->getNote();
            $maxScore += $response->getQuestion()->getPoint();
        }

        if (!$allCorrected) {
            throw $this->createAccessDeniedException('L\'évaluation n\'est pas encore corrigée.');
        }

        // Générer le PDF
        return $pdfService->generateEvaluationPdf($evaluation, $user);
    }
    #[Route('/etudiant/{id}', name: 'app_evaluation_showetudiant', methods: ['GET', 'POST'])]
    public function showEtudiant(
        UserRepository $userRepository,
        Request $request,
        Evaluation $evaluation,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager,
        ReponseRepository $reponseRepository
    ): Response {
        // Récupérer l'utilisateur connecté
        $session = $request->getSession();
        $userId = $session->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer les questions
        $questions = $questionRepository->findBy(
            ['evaluation' => $evaluation],
            ['ordreQuestion' => 'ASC']
        );

        if (empty($questions)) {
            throw $this->createNotFoundException('Aucune question trouvée pour cette évaluation.');
        }

        // Calculer le score maximum total
        $maxScore = 0;
        foreach ($questions as $question) {
            $maxScore += $question->getPoint();
        }

        // Vérifier si l'étudiant a déjà répondu
        $existingResponses = $reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $user
        ]);

        $hasResponded = !empty($existingResponses);
        $isCorrected = true;
        $reponses = [];
        $totalScore = 0;
        $hasInappropriateContent = false;

        // Si l'étudiant a déjà répondu, préparer les réponses pour l'affichage
        if ($hasResponded) {
            foreach ($existingResponses as $reponse) {
                $reponses[$reponse->getQuestion()->getId()] = $reponse;
                if ($reponse->getNote() === null) {
                    $isCorrected = false;
                } else {
                    $totalScore += $reponse->getNote();
                    
                    // Vérifier si la réponse contient du contenu inapproprié
                    if ($reponse->getNote() === 0 && str_starts_with($reponse->getCommentaire(), 'AVERTISSEMENT')) {
                        $hasInappropriateContent = true;
                    }
                }
            }
        }

        return $this->render('evaluation/showEtudiant.html.twig', [
            'evaluation' => $evaluation,
            'questions' => $questions,
            'hasResponded' => $hasResponded,
            'reponses' => $reponses,
            'isCorrected' => $isCorrected,
            'hasInappropriateContent' => $hasInappropriateContent,
            'totalScore' => $totalScore,
            'maxScore' => $maxScore
        ]);
    }

    #[Route('/etudiant/repondre/{id}', name: 'etudiant_evaluation_answer', methods: ['GET', 'POST'])]
    public function answerEtudiant(
        int $id, 
        Request $request, 
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        ProfanityCheckerService $profanityChecker,
        MailerInterface $mailer,
        UserRepository $userRepository,
        LoggerInterface $logger
    ): Response {
        // Récupérer l'évaluation
        $evaluation = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        // Récupérer l'utilisateur connecté (l'étudiant)
        $session = $request->getSession();
        $userId = $session->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Vérifier si l'étudiant a déjà répondu à cette évaluation
        $existingReponses = $entityManager->getRepository(Reponse::class)->findBy([
            'user' => $user,
            'evaluation' => $evaluation
        ]);

        // Si l'étudiant a déjà soumis des réponses, le rediriger vers la page des résultats
        if (count($existingReponses) > 0) {
            $this->addFlash('warning', 'Vous avez déjà répondu à cette évaluation.');
            return $this->redirectToRoute('app_evaluation_showetudiant', ['id' => $id]);
        }

        // Vérifier si la date limite est dépassée
        if ($evaluation->getDeadline() < new \DateTime()) {
            $this->addFlash('error', 'Le délai pour répondre à cette évaluation est dépassé.');
            return $this->redirectToRoute('app_evaluation_indexetudiant');
        }

        // Log pour déboguer
        $logger->info('Début de la soumission d\'une réponse', [
            'evaluation_id' => $id,
            'user_id' => $userId,
            'user_email' => $user->getEmail()
        ]);

        // Convertir les questions en tableau pour la pagination
        $questions = $evaluation->getQuestions()->toArray();
        
        // Configurer la pagination
        $pagination = $paginator->paginate(
            $questions,
            $request->query->getInt('page', 1),
            5 // nombre de questions par page
        );

        // Stocker les réponses dans la session
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $session->set('evaluation_' . $id . '_responses', $data['reponses'] ?? []);
            
            // Si nous sommes sur la dernière page, traiter la soumission
            if ($request->query->getInt('page') >= ceil(count($questions) / 5)) {
                $allResponses = $session->get('evaluation_' . $id . '_responses', []);
                $hasInappropriateContent = false;
                
                foreach ($questions as $question) {
                    if (!isset($allResponses[$question->getId()]) || 
                        trim($allResponses[$question->getId()]) === '') {
                        $this->addFlash('error', 'Veuillez répondre à toutes les questions.');
                        return $this->redirectToRoute('etudiant_evaluation_answer', [
                            'id' => $id,
                            'page' => $pagination->getCurrentPageNumber()
                        ]);
                    }

                    $reponse = new Reponse();
                    $reponse->setQuestion($question)
                           ->setText(trim($allResponses[$question->getId()]))
                           ->setUser($user)
                           ->setEvaluation($evaluation)
                           ->setSubmitTime(new \DateTime());

                    if ($profanityChecker->containsProfanity($allResponses[$question->getId()])) {
                        $hasInappropriateContent = true;
                        $reponse->setPlagiatSuspect(true)
                               ->setPlagiatDetails('Contenu inapproprié détecté');
                        
                        // Récupérer l'enseignant du cours associé à l'évaluation
                        $teacher = $evaluation->getCours()->getEnseignant();
                        if ($teacher) {
                            // Envoyer un email à l'enseignant
                            $this->mailService->sendInappropriateContentAlert(
                                $user,
                                $evaluation,
                                $question,
                                $allResponses[$question->getId()],
                                $teacher
                            );
                        }
                    }

                    $entityManager->persist($reponse);
                }
                
                $entityManager->flush();
                $session->remove('evaluation_' . $id . '_responses');
                
                if ($hasInappropriateContent) {
                    $this->addFlash('warning', 'Attention : Certaines de vos réponses contiennent un langage inapproprié.');
                } else {
                    $this->addFlash('success', 'Vos réponses ont été enregistrées avec succès.');
                }
                
                return $this->redirectToRoute('app_evaluation_showetudiant', ['id' => $id]);
            }
            
            // Sinon, rediriger vers la page suivante
            return $this->redirectToRoute('etudiant_evaluation_answer', [
                'id' => $id,
                'page' => $request->query->getInt('page') + 1
            ]);
        }

        // Récupérer les réponses précédemment stockées
        $savedResponses = $session->get('evaluation_' . $id . '_responses', []);

        return $this->render('evaluation/answer.html.twig', [
            'evaluation' => $evaluation,
            'pagination' => $pagination,
            'reponses' => $savedResponses
        ]);
    }

    #[Route('/etudiant/{id}/submit', name: 'app_evaluation_submit', methods: ['POST'])]
    public function submitEvaluation(
        Request $request,
        Evaluation $evaluation,
        EntityManagerInterface $entityManager
    ): Response {
        // Envoyer l'email de confirmation de soumission
        $this->mailService->sendEvaluationSubmittedToStudent($this->getUser(), $evaluation);
        
        $this->addFlash('success', 'L\'évaluation a été soumise avec succès.');
        return $this->redirectToRoute('app_evaluation_show', ['id' => $evaluation->getId()]);
    }

    #[Route('/enseignant/evaluation/{evaluationId}/student/{studentId}/correct', name: 'app_evaluation_correct_submit', methods: ['POST'])]
    public function submitCorrection(
        Request $request,
        int $evaluationId,
        int $studentId,
        EvaluationRepository $evaluationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $evaluation = $evaluationRepository->find($evaluationId);

        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        // Récupérer l'étudiant et envoyer la notification
        $student = $entityManager->getRepository(User::class)->find($studentId);
        if ($student) {
            $evaluation = $entityManager->getRepository(Evaluation::class)->find($evaluationId);
            $this->mailService->sendEvaluationGradedNotification($student, $evaluation);
        }

        $this->addFlash('success', 'La correction a été soumise avec succès.');
        return $this->redirectToRoute('evaluation_correctenseignant', ['id' => $evaluationId]);
    }

    private function isEvaluationGradedForStudent(Evaluation $evaluation): bool
    {
        // Vérifier si toutes les réponses ont un score
        foreach ($evaluation->getQuestions() as $question) {
            $response = $question->getReponse();
            if (!$response || $response->getScore() === null) {
                return false;
            }
        }
        return true;
    }

    private function calculateStudentScore(Evaluation $evaluation): int
    {
        $totalScore = 0;
        foreach ($evaluation->getQuestions() as $question) {
            $response = $question->getReponse();
            if ($response && $response->getScore() !== null) {
                $totalScore += $response->getScore();
            }
        }
        return $totalScore;
    }
    
    // Routes pour l'étudiant
    #[Route('/etudiant', name: 'app_evaluation_indexetudiant', methods: ['GET'])]
    public function indexEtudiant(
        EvaluationRepository $evaluationRepository,
        ReponseRepository $reponseRepository
    ): Response {
        $evaluations = $evaluationRepository->findAll();
        
        // Préparer les données pour le template
        $evaluationsData = [];
        foreach ($evaluations as $evaluation) {
            $hasResponded = false;
            $isCorrected = true;
            $reponses = $reponseRepository->findBy(['evaluation' => $evaluation]);
            
            if (!empty($reponses)) {
                $hasResponded = true;
                // Vérifier si toutes les réponses ont une note
                foreach ($reponses as $reponse) {
                    if ($reponse->getNote() === null) {
                        $isCorrected = false;
                        break;
                    }
                }
            } else {
                $isCorrected = false;
            }
            
            $evaluationsData[] = [
                'id' => $evaluation->getId(),
                'title' => $evaluation->getTitle(),
                'description' => $evaluation->getDescription(),
                'maxScore' => $evaluation->getMaxScore(),
                'deadline' => $evaluation->getDeadline(),
                'hasResponded' => $hasResponded,
                'isCorrected' => $isCorrected
            ];
        }
        
        return $this->render('evaluation/indexEtudiant.html.twig', [
            'evaluations' => $evaluationsData
        ]);
    }

    private function isEvaluationCorrected(Evaluation $evaluation, ReponseRepository $reponseRepository): bool
    {
        $allResponses = [];
        foreach ($evaluation->getQuestions() as $question) {
            $responses = $reponseRepository->findBy([
                'question' => $question,
            ]);
            $allResponses = array_merge($allResponses, $responses);
        }

        if (empty($allResponses)) {
            return false;
        }

        foreach ($allResponses as $response) {
            if (!$response->isCorrect()) {
                return false;
            }
        }

        return true;
    }

    #[Route('/enseignant/details/{id}', name: 'app_evaluation_detailsenseignant', methods: ['GET'])]
    public function detailsEnseignant(Evaluation $evaluation): Response
    {
        return $this->render('evaluation/showenseignant.html.twig', [
            'evaluation' => $evaluation
        ]);
    }

    #[Route('/test-email', name: 'app_evaluation_test_email')]
    public function testEmail(): Response
    {
        try {
            $evaluation = new Evaluation();
            $evaluation->setTitle("Test d'évaluation");
            
            // Test d'envoi d'email de soumission
            $this->mailService->sendEvaluationSubmissionNotification($evaluation);
            
            $this->addFlash('success', 'Email de test envoyé avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_evaluation_indexetudiant');
    }

    private function calculateMedian(array $scores): float
    {
        if (empty($scores)) {
            return 0;
        }
        
        sort($scores);
        $count = count($scores);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($scores[$middle - 1] + $scores[$middle]) / 2;
        }

        return $scores[$middle];
    }

    private function calculateStandardDeviation(array $scores): float
    {
        if (empty($scores)) {
            return 0;
        }

        $mean = array_sum($scores) / count($scores);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $scores)) / count($scores);

        return sqrt($variance);
    }

    private function calculatePassingRate(array $scores, float $passingScore): float
    {
        if (empty($scores)) {
            return 0;
        }

        $passing = count(array_filter($scores, function($score) use ($passingScore) {
            return $score >= $passingScore;
        }));

        return $passing / count($scores);
    }

    #[Route('/evaluation/confirm-inappropriate/{evaluationId}/{reponseId}', name: 'app_evaluation_confirm_inappropriate', methods: ['POST'])]
    public function confirmInappropriateContent(
        Request $request,
        int $evaluationId,
        int $reponseId,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $reponse = $entityManager->getRepository(Reponse::class)->find($reponseId);
        
        if (!$reponse) {
            throw $this->createNotFoundException('Réponse non trouvée');
        }

        // Mettre la note à 0
        $reponse->setNote(0);
        
        // Ajouter le message d'avertissement
        $warningMessage = $request->request->get('warning_message');
        $reponse->setCommentaire(sprintf(
            "AVERTISSEMENT : Contenu inapproprié confirmé.\n%s\nNote automatiquement mise à 0.",
            $warningMessage
        ));

        // Envoyer un email d'avertissement à l'étudiant via MailService
        $this->mailService->sendInappropriateContentWarningToStudent(
            $reponse->getUser(),
            $reponse->getEvaluation(),
            $reponse->getQuestion(),
            $warningMessage
        );

        $entityManager->flush();

        $this->addFlash('warning', 'La réponse a été marquée comme inappropriée et la note a été mise à 0.');
        
        return $this->redirectToRoute('evaluation_correctenseignant', [
            'id' => $evaluationId
        ]);
    }

    #[Route('/etudiant/{evaluationId}', name: 'app_evaluation_show_student_result')]
    public function showStudentResult(
        int $evaluationId,
        EvaluationRepository $evaluationRepository,
        ReponseRepository $reponseRepository
    ): Response {
        $evaluation = $evaluationRepository->find($evaluationId);
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        $user = $this->getUser();
        $reponses = $reponseRepository->findBy([
            'evaluation' => $evaluation,
            'user' => $user
        ]);

        $hasInappropriateContent = false;
        $totalScore = 0;
        $maxScore = 0;

        foreach ($reponses as $reponse) {
            $maxScore += $reponse->getQuestion()->getPoint();
            if ($reponse->getStatus() === 'corrige') {
                $totalScore += $reponse->getNote();
                if ($reponse->getNote() === 0 && str_starts_with($reponse->getCommentaire(), 'AVERTISSEMENT')) {
                    $hasInappropriateContent = true;
                }
            }
        }

        return $this->render('evaluation/show_result.html.twig', [
            'evaluation' => $evaluation,
            'reponses' => $reponses,
            'totalScore' => $totalScore,
            'maxScore' => $maxScore,
            'hasInappropriateContent' => $hasInappropriateContent
        ]);
    }

  

    #[Route('/evaluation/recorrect/{id}', name: 'app_evaluation_recorrect', methods: ['GET'])]
    public function recorrect(
        Evaluation $evaluation,
        QuestionRepository $questionRepository,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les questions et les réponses déjà corrigées
        $questions = $questionRepository->findBy(['evaluation' => $evaluation], ['ordreQuestion' => 'ASC']);
        $responses = $reponseRepository->findBy(['evaluation' => $evaluation]);

        // Organiser les réponses par étudiant
        $studentResponses = [];
        foreach ($responses as $response) {
            $studentId = $response->getUser()->getId();
            if (!isset($studentResponses[$studentId])) {
                $studentResponses[$studentId] = [
                    'student' => $response->getUser(),
                    'responses' => [],
                    'isFullyCorrected' => true
                ];
            }
            $studentResponses[$studentId]['responses'][] = $response;
            
            // Vérifier si toutes les réponses sont corrigées
            if ($response->getNote() === null) {
                $studentResponses[$studentId]['isFullyCorrected'] = false;
            }
        }

        return $this->render('evaluation/recorrect.html.twig', [
            'evaluation' => $evaluation,
            'questions' => $questions,
            'studentResponses' => $studentResponses
        ]);
    }
}