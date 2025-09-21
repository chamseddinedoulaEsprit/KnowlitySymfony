<?php

namespace App\Controller;
use App\Entity\User;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Form\ChapitreType;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/chapitre')]
final class ChapitreController extends AbstractController
{
    #[Route(name: 'app_chapitre_index', methods: ['GET'])]
    public function index(ChapitreRepository $chapitreRepository): Response
    {
        return $this->render('chapitre/index1.html.twig', [
            'chapitres' => $chapitreRepository->findAll(),
        ]);
    }
    #[Route('/etu/{id}',name: 'app_chapitre_indexEtu', methods: ['GET'])]
    public function indexetu(ChapitreRepository $chapitreRepository,$id): Response
    {
        return $this->render('chapitre/index2.html.twig', [
            'chapitres' => $chapitreRepository->findByCours($id),
        ]);
    }
//enseignant
    #[Route('/new/{id}', name: 'app_chapitre_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, $id, SluggerInterface $slugger,
#[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory): Response
{
    // Récupérer le cours correspondant à l'ID
    $cours = $entityManager->getRepository(Cours::class)->find($id);

    // Vérifier si le cours existe
    

    // Créer un nouveau chapitre
    $chapitre = new Chapitre();
    $chapitre->setCours($cours);
     // Associer le cours au chapitre
     $chapitre->setNbrVues(0);
    // Créer le formulaire
    $form = $this->createForm(ChapitreType::class, $chapitre);
    $form->handleRequest($request);

    // Traiter la soumission du formulaire
    if ($form->isSubmitted() && $form->isValid()) {
        // Persister le chapitre
        $brochureFile = $form->get('brochure')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $chapitre->setContenu($newFilename);
            }
        $entityManager->persist($chapitre);
        $entityManager->flush();

        // Vérifier quel bouton a été cliqué
        if ($request->request->has('save')) {
            // Rediriger vers la liste des chapitres
            return $this->redirectToRoute('app_cours', ['id' => $id], Response::HTTP_SEE_OTHER);
        } elseif ($request->request->has('add_another')) {
            // Rediriger vers la page de création d'un nouveau chapitre
            return $this->redirectToRoute('app_chapitre_new', ['id' => $id], Response::HTTP_SEE_OTHER);
        }
    } elseif ($request->request->has('add_another_evaluation')) {
        // Rediriger vers la page de création d'un nouveau chapitre
        return $this->redirectToRoute('app_evaluation_newenseignant', ['coursId' => $id], Response::HTTP_SEE_OTHER);
    }
    

    // Afficher le formulaire
    return $this->render('chapitre/new.html.twig', [
        'chapitre' => $chapitre,
        'form' => $form,
    ]);
}
//etudiant
#[Route('/etudiant/{id}', name: 'app_etudiant', methods: ['GET'])]
public function showEtu(Chapitre $chapitre, EntityManagerInterface $entityManager, Request $request): Response
{
    $session = $request->getSession();

    // Vérifiez si l'utilisateur a déjà vu ce chapitre
    $viewedChapters = $session->get('viewed_chapters', []);
    if (!in_array($chapitre->getId(), $viewedChapters)) {
        // Incrémenter le nombre de vues
        $chapitre->incrementNbrVues();

        // Sauvegarder les modifications
        $entityManager->persist($chapitre);
        $entityManager->flush();

        // Marquer le chapitre comme vu dans la session
        $viewedChapters[] = $chapitre->getId();
        $session->set('viewed_chapters', $viewedChapters);
    }

    return $this->render('chapitre/show.html.twig', [
        'chapitre' => $chapitre,
    ]);
}

//enseignant
    #[Route('/{id}', name: 'app_chapitre_show', methods: ['GET'])]
    public function show(Chapitre $chapitre): Response
    {
        return $this->render('chapitre/showPourEnseignant.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }
//enseignant
#[Route('/{id}/edit', name: 'app_chapitre_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    Chapitre $chapitre,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory
): Response {
    $userId = $request->getSession()->get('id');
    $user = $entityManager->getRepository(User::class)->find($userId);

    if (!$user) {
        $this->addFlash('error', 'Utilisateur non trouvé.');
        return $this->redirectToRoute('app_login');
    }

    $enseignant = $chapitre->getCours()->getEnseignant();

    if ($user->getId() !== $enseignant->getId() && $user->getRoles()!=="Admin") {
        $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de modifier ce chapitre.');
        return $this->redirectToRoute('app_login');
    }

    $form = $this->createForm(ChapitreType::class, $chapitre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gérer l'upload du fichier
        $brochureFile = $form->get('brochure')->getData();

        if ($brochureFile) {
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

            // Déplacer le fichier dans le répertoire de stockage
            try {
                $brochureFile->move($brochuresDirectory, $newFilename);

                // Supprimer l'ancien fichier s'il existe
                $oldFilename = $chapitre->getContenu();
                if ($oldFilename && file_exists($brochuresDirectory . '/' . $oldFilename)) {
                    unlink($brochuresDirectory . '/' . $oldFilename);
                }

                // Mettre à jour le nom du fichier dans l'entité
                $chapitre->setContenu($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de l\'upload du fichier.');
                return $this->redirectToRoute('app_chapitre_edit', ['id' => $chapitre->getId()]);
            }
        }

        // Enregistrer les modifications en base de données
        $entityManager->flush();

        $this->addFlash('success', 'Le chapitre a été modifié avec succès.');
        return $this->redirectToRoute('app_cours', ['id' => 1], Response::HTTP_SEE_OTHER);
    }

    return $this->render('chapitre/edit.html.twig', [
        'chapitre' => $chapitre,
        'form' => $form->createView(),
    ]);
}

    //enseignant
    #[Route('/{id}', name: 'app_chapitre_delete', methods: ['POST'])]
    public function delete(Request $request, Chapitre $chapitre, EntityManagerInterface $entityManager): Response
    {

        if ($this->isCsrfTokenValid('delete'.$chapitre->getId(), $request->getPayload()->getString('_token'))) {
            $cours=$chapitre->getCours();
            $entityManager->remove($chapitre);
            $entityManager->flush();
        }
$id=$cours->getId();
        return $this->redirectToRoute('app_cours', ['id'=>$id], Response::HTTP_SEE_OTHER);
    }
}
