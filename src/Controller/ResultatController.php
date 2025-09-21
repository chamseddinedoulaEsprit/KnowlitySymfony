<?php

namespace App\Controller;

use App\Entity\Resultat;
use App\Entity\Reponse;
use App\Form\ResultatType;
use App\Repository\ResultatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/resultat')]
final class ResultatController extends AbstractController
{
    #[Route(name: 'app_resultat_index', methods: ['GET'])]
    public function index(ResultatRepository $resultatRepository): Response
    {
        return $this->render('resultat/index.html.twig', [
            'resultats' => $resultatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_resultat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class, $resultat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($resultat);
            $entityManager->flush();

            return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('resultat/new.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_resultat_show', methods: ['GET'])]
    public function show(Resultat $resultat): Response
    {
        return $this->render('resultat/show.html.twig', [
            'resultat' => $resultat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_resultat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Resultat $resultat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResultatType::class, $resultat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('resultat/edit.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/validate', name: 'app_resultat_validate', methods: ['POST'])]
    public function validate(Resultat $resultat, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponses = $resultat->getReponses(); // Récupérer toutes les réponses associées au résultat
        $totalScore = 0;

        foreach ($reponses as $reponse) {
            $note = $request->request->get('note_' . $reponse->getId()); // Accéder à la note

            if ($note !== null) {
                $reponse->setNote((int) $note); // Attribuer la note à la réponse
                $totalScore += (int) $note; // Ajouter au total

                // Lier la réponse au résultat
                $reponse->setResultat($resultat);
            }
        }

        // Mettre à jour le score du résultat
        $resultat->setScore($totalScore);

        $entityManager->flush();

        return $this->redirectToRoute('app_resultat_show', ['id' => $resultat->getId()]);
    }

    #[Route('/{id}', name: 'app_resultat_delete', methods: ['POST'])]
    public function delete(Request $request, Resultat $resultat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$resultat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($resultat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
    }
}