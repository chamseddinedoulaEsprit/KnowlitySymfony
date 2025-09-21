<?php

namespace App\Controller;

use App\Entity\QuizQuestion;
use App\Entity\Quiz;
use App\Entity\QuizResponse;
use App\Form\QuizResponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quizresponse')]
final class QuizResponseController extends AbstractController
{
    #[Route(name: 'app_quiz_response_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $quizResponses = $entityManager
            ->getRepository(QuizResponse::class)
            ->findAll();

        return $this->render('quiz_response/index.html.twig', [
            'quiz_responses' => $quizResponses,
        ]);
    }

    #[Route('/new/{id}', name: 'app_quiz_response_new', methods: ['GET', 'POST'])]
    public function new($id,Request $request, EntityManagerInterface $entityManager): Response
    {
        $quizResponse = new QuizResponse();
        $question=$entityManager->getRepository(QuizQuestion::class)->find($id);
        $quizResponse->setQuestion($question);
       
        $form = $this->createForm(QuizResponseType::class, $quizResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quizResponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_response_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quiz_response/new.html.twig', [
            'quiz_response' => $quizResponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_response_show', methods: ['GET'])]
    public function show(QuizResponse $quizResponse): Response
    {
        return $this->render('quiz_response/show.html.twig', [
            'quiz_response' => $quizResponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quiz_response_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuizResponse $quizResponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuizResponseType::class, $quizResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_response_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quiz_response/edit.html.twig', [
            'quiz_response' => $quizResponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_response_delete', methods: ['POST'])]
    public function delete(Request $request, QuizResponse $quizResponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quizResponse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($quizResponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quiz_response_index', [], Response::HTTP_SEE_OTHER);
    }
}
