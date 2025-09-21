<?php

namespace App\Controller;

use App\Entity\UserQuizSelection;
use App\Form\UserQuizSelectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserQuizSelectionRepository;
use Knp\Snappy\Pdf;
#[Route('/user/quiz/selection')]
final class UserQuizSelectionController extends AbstractController
{private Pdf $pdf;
    private UserQuizSelectionRepository $userQuizSelectionRepository;

    public function __construct(Pdf $pdf, UserQuizSelectionRepository $userQuizSelectionRepository)
    {
        $this->pdf = $pdf;
        $this->userQuizSelectionRepository = $userQuizSelectionRepository;
    }

    #[Route('/quiz-results/pdf/{id}', name: 'generate_pdf')]
    public function generatePdf(int $id): Response
    {
        $userQuizSelection = $this->userQuizSelectionRepository->find($id);

        if (!$userQuizSelection) {
            throw $this->createNotFoundException("Quiz selection not found.");
        }

        // Générer le HTML du template
        $html = $this->renderView('user_quiz_selection/pdf_template.html.twig', [
            'user_quiz_selection' => $userQuizSelection
        ]);

        // Générer le PDF
        $pdfContent = $this->pdf->getOutputFromHtml($html);

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="quiz_results.pdf"',
            ]
        );
    }
    #[Route(name: 'app_user_quiz_selection_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userQuizSelections = $entityManager
            ->getRepository(UserQuizSelection::class)
            ->findAll();

        return $this->render('user_quiz_selection/index.html.twig', [
            'user_quiz_selections' => $userQuizSelections,
        ]);
    }

    #[Route('/new', name: 'app_user_quiz_selection_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userQuizSelection = new UserQuizSelection();
        $form = $this->createForm(UserQuizSelectionType::class, $userQuizSelection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userQuizSelection);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quiz_selection_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quiz_selection/new.html.twig', [
            'user_quiz_selection' => $userQuizSelection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quiz_selection_show', methods: ['GET'])]
    public function show(UserQuizSelection $userQuizSelection, Request $request): Response
    {
        // Retrieve the responses from the session
        $session = $request->getSession();
        $responses = $session->get('quiz_responses', []);
    
        return $this->render('user_quiz_selection/show.html.twig', [
            'user_quiz_selection' => $userQuizSelection,
            'responses' => $responses, // Pass the responses to the template
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_quiz_selection_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserQuizSelection $userQuizSelection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserQuizSelectionType::class, $userQuizSelection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quiz_selection_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quiz_selection/edit.html.twig', [
            'user_quiz_selection' => $userQuizSelection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quiz_selection_delete', methods: ['POST'])]
    public function delete(Request $request, UserQuizSelection $userQuizSelection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userQuizSelection->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userQuizSelection);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_quiz_selection_index', [], Response::HTTP_SEE_OTHER);
    }
}
