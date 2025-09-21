<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/question')]
final class QuestionController extends AbstractController
{
    #[Route('/showquestions/{evaluationId}',name: 'app_question_index', methods: ['GET'])]
    public function index($evaluationId, QuestionRepository $questionRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'évaluation
        $evaluation = $entityManager->getRepository(Evaluation::class)->find($evaluationId);

        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        // Récupérer les questions triées par ordre
        $questions = $questionRepository->findBy(
            ['evaluation' => $evaluation],
            ['ordreQuestion' => 'ASC']
        );

        return $this->render('question/index.html.twig', [
            'questions' => $questions,
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/new/{evaluationId}', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $evaluationId): Response
    {
        $evaluation = $entityManager->getRepository(Evaluation::class)->find($evaluationId);
        
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation non trouvée');
        }

        $question = new Question();
        $question->setEvaluation($evaluation);

        // Définir l'ordre automatiquement
        $lastQuestion = $entityManager->getRepository(Question::class)
            ->findOneBy(['evaluation' => $evaluation], ['ordreQuestion' => 'DESC']);
        $question->setOrdreQuestion($lastQuestion ? $lastQuestion->getOrdreQuestion() + 1 : 1);

        $form = $this->createForm(QuestionType::class, $question);
        
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['question']['enonce'])) {
                $question->setEnonce($data['question']['enonce']);
            }
            
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $entityManager->persist($question);
                    $entityManager->flush();

                    $this->addFlash('success', 'Question créée avec succès !');

                    if ($request->request->has('add_another')) {
                        return $this->redirectToRoute('app_question_new', ['evaluationId' => $evaluationId]);
                    }

                    return $this->redirectToRoute('app_question_index', ['evaluationId' => $evaluationId]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de la question : ' . $e->getMessage());
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'evaluation' => $evaluation,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_question_index', ['evaluationId' => $question->getEvaluation()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
            'editMode' => true
        ]);
    }

   #[Route('/{id}', name: 'app_question_delete', methods: ['POST'])]
public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
{
    $evaluationId = $question->getEvaluation()->getId(); // Récupération de l'évaluation associée

    if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->get('token'))) {
        $entityManager->remove($question);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_question_index', ['evaluationId' => $evaluationId], Response::HTTP_SEE_OTHER);
}


    #[Route('/{id}/repondre', name: 'app_question_repondre', methods: ['GET'])]
    public function repondre(int $id, EntityManagerInterface $entityManager): Response
    {
        $question = $entityManager->getRepository(Question::class)->find($id);
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        return $this->redirectToRoute('app_reponse_new', ['questionId' => $id]);
    }

    #[Route('/preview-code', name: 'app_question_preview_code', methods: ['POST'])]
    public function previewCode(Request $request): JsonResponse
    {
        $code = $request->request->get('code');
        $language = $request->request->get('language');
        
        return new JsonResponse([
            'code' => $code,
            'language' => $language
        ]);
    }
}