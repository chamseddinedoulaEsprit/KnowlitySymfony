<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\QuizQuestion;
use App\Entity\QuizResponse;
use App\Form\QuizQuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/q/u/i/z/q/u/e/s/t/i/o/n')]
final class QuizQuestionController extends AbstractController
{
    #[Route(name: 'app_q_u_i_z_q_u_e_s_t_i_o_n_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $qUIZQUESTIONs = $entityManager
            ->getRepository(QuizQuestion::class)
            ->findAll();

        return $this->render('quizquestion/index.html.twig', [
            'q_u_i_z_q_u_e_s_t_i_o_ns' => $qUIZQUESTIONs,
        ]);
    }

   // src/Controller/QUIZQUESTIONController.php
   #[Route('/new/{id}', name: 'app_q_u_i_z_q_u_e_s_t_i_o_n_new', methods: ['GET', 'POST'])]
   public function new($id, Request $request, EntityManagerInterface $entityManager): Response
   {
       $quiz = $entityManager->getRepository(Quiz::class)->find($id);
       if (!$quiz) {
           throw $this->createNotFoundException('Quiz not found.');
       }
   
       $question = new QuizQuestion();
       $question->setQuiz($quiz);
   
       // Add 4 empty responses
       for ($i = 0; $i < 4; $i++) {
           $response = new QuizResponse();
           $response->setQuestion($question);
           $question->addReponse($response);
       }
   
       $form = $this->createForm(QuizQuestionType::class, $question);
       $form->handleRequest($request);
   
       if ($form->isSubmitted() && $form->isValid()) {
           $entityManager->persist($question);
           $entityManager->flush();
   
           // Check which button was clicked
           if ($form->get('add_another')->isClicked()) {
               // Redirect to the same page to add another question
               return $this->redirectToRoute('app_q_u_i_z_q_u_e_s_t_i_o_n_new', ['id' => $id]);
           }
   
           // Redirect to the quiz index page after saving
           return $this->redirectToRoute('app_cours', ['id' => $quiz->getCour()->getId()]);
       }
   
       return $this->render('quizquestion/new.html.twig', [
           'form' => $form->createView(),
           'quiz' => $quiz, // Pass the quiz to the template
       ]);
   }
    #[Route('/{id}', name: 'app_q_u_i_z_q_u_e_s_t_i_o_n_show', methods: ['GET'])]
    public function show(QuizQuestion $qUIZQUESTION): Response
    {
        return $this->render('quizquestion/show.html.twig', [
            'q_u_i_z_q_u_e_s_t_i_o_n' => $qUIZQUESTION,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_q_u_i_z_q_u_e_s_t_i_o_n_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuizQuestion $qUIZQUESTION, EntityManagerInterface $entityManager): Response
    {
        $quiz = $qUIZQUESTION->getQuiz();  // Get the related quiz for the question
        
        // Add empty responses if they don't exist
        if ($qUIZQUESTION->getReponses()->count() < 4) {
            for ($i = $qUIZQUESTION->getReponses()->count(); $i < 4; $i++) {
                $response = new QuizResponse();
                $response->setQuestion($qUIZQUESTION);
                $qUIZQUESTION->addReponse($response);
            }
        }
    
        $form = $this->createForm(QuizQuestionType::class, $qUIZQUESTION);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();  // Save the updated question
    
            // Redirect to the quiz index page
            return $this->redirectToRoute('app_q_u_i_z_q_u_e_s_t_i_o_n_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('quizquestion/edit.html.twig', [
            'form' => $form->createView(),
            'quiz' => $quiz,  // Pass the quiz to the template
        ]);
    }
    
    #[Route('/{id}', name: 'app_q_u_i_z_q_u_e_s_t_i_o_n_delete', methods: ['POST'])]
    public function delete(Request $request, QuizQuestion $qUIZQUESTION, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$qUIZQUESTION->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($qUIZQUESTION);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_q_u_i_z_q_u_e_s_t_i_o_n_index', [], Response::HTTP_SEE_OTHER);
    }
}
