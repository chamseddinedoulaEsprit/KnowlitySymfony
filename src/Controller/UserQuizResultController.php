<?php

namespace App\Controller;

use App\Entity\UserQuizResult;
use App\Form\UserQuizResultType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/quiz/result')]
final class UserQuizResultController extends AbstractController
{
    #[Route(name: 'app_user_quiz_result_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userQuizResults = $entityManager
            ->getRepository(UserQuizResult::class)
            ->findAll();

        return $this->render('user_quiz_result/index.html.twig', [
            'user_quiz_results' => $userQuizResults,
        ]);
    }

    #[Route('/new', name: 'app_user_quiz_result_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userQuizResult = new UserQuizResult();
        $form = $this->createForm(UserQuizResultType::class, $userQuizResult);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userQuizResult);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quiz_result_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quiz_result/new.html.twig', [
            'user_quiz_result' => $userQuizResult,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quiz_result_show', methods: ['GET'])]
    public function show(UserQuizResult $userQuizResult): Response
    {
        return $this->render('user_quiz_result/show.html.twig', [
            'user_quiz_result' => $userQuizResult,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_quiz_result_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserQuizResult $userQuizResult, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserQuizResultType::class, $userQuizResult);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quiz_result_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quiz_result/edit.html.twig', [
            'user_quiz_result' => $userQuizResult,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quiz_result_delete', methods: ['POST'])]
    public function delete(Request $request, UserQuizResult $userQuizResult, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userQuizResult->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userQuizResult);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_quiz_result_index', [], Response::HTTP_SEE_OTHER);
    }
}
