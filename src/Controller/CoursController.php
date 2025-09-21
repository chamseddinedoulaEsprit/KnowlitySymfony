<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Cours;
use App\Entity\Matiere;
use App\Entity\User;
use App\Form\CoursType;
use App\Repository\MatiereRepository;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\PdfGenerator;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

#[Route('/cours')]
final class CoursController extends AbstractController
{
   
    #[Route(name: 'app_cours_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository, MatiereRepository $matiereRepository): Response
    {
        return $this->render('cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
            'matieres' => $matiereRepository->findAll(),
        ]);
    }
     // etudiant
 
     #[Route('/cours', name: 'app_cours_index2', methods: ['GET'])]
     public function index2(
         Request $request,
         CoursRepository $coursRepository,
         MatiereRepository $matiereRepository,
         PaginatorInterface $paginator
     ): Response {
         // Création du QueryBuilder de base
         $queryBuilder = $coursRepository->createQueryBuilder('c')
             ->leftJoin('c.matiere', 'm')->addSelect('m')
             ->leftJoin('c.enseignant', 'e')->addSelect('e')
             ->leftJoin('c.chapitres', 'ch')
             ->leftJoin('c.etudiants', 'et')
             ->groupBy('c.id')
             ->addSelect('COUNT(DISTINCT ch.id) AS HIDDEN nbChapitres')
             ->addSelect('COUNT(DISTINCT et.id) AS HIDDEN NbEtudiants');
     
         // Gestion des paramètres de recherche
         $searchTerm = $request->query->get('search');
         $matiereId = $request->query->get('matiere');
     
         // Filtre par terme de recherche
         if ($searchTerm) {
             $queryBuilder
                 ->andWhere('c.title LIKE :searchTerm')
                 ->setParameter('searchTerm', '%' . $searchTerm . '%');
         }
     
         // Filtre par matière
         if ($matiereId && is_numeric($matiereId)) {
             $queryBuilder
                 ->andWhere('m.id = :matiereId')
                 ->setParameter('matiereId', $matiereId);
         }
     
         // Gestion du tri
         $sortField = $request->query->get('sort_by', 'c.title');
         $sortDirection = $request->query->get('sort_order', 'ASC');
     
         $allowedSorts = [
             'c.title' => 'c.title',
             'nbChapitres' => 'nbChapitres',
             'NbEtudiants' => 'NbEtudiants'
         ];
     
         if (array_key_exists($sortField, $allowedSorts)) {
             $queryBuilder->orderBy($allowedSorts[$sortField], $sortDirection);
         }
     
         // Pagination
         $pagination = $paginator->paginate(
             $queryBuilder->getQuery(),
             $request->query->getInt('page', 1),
             9,
             [
                 'wrap-queries' => true,
                 'sortFieldAllowList' => array_keys($allowedSorts)
             ]
         );
     
         return $this->render('cours/index.html.twig', [
             'pagination' => $pagination,
             'matieres' => $matiereRepository->findAll(),
             'current_sort' => [
                 'field' => $sortField,
                 'direction' => $sortDirection
             ]
         ]);
     }
    #[Route('/cours3', name: 'app_cours_index3', methods: ['GET'])]
    public function index3(
        Request $request,
        CoursRepository $coursRepository,
        MatiereRepository $matiereRepository,
        UserRepository $userRepository
    ): Response {
        $session = $request->getSession();
        $userId = $session->get('id'); // Récupère l'ID de l'utilisateur depuis la session
    
        if (!$userId) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }
    
        $user = $userRepository->find($userId);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }
    
        // Récupère les cours où l'utilisateur est inscrit
        $cours = $coursRepository->createQueryBuilder('c')
            ->innerJoin('c.etudiants', 'e') // Joindre la relation "etudiants"
            ->where('e.id = :userId') // Filtrer par l'ID de l'utilisateur
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    
        return $this->render('cours/index3.html.twig', [
            'cours' => $cours,
            'matieres' => $matiereRepository->findAll(),
        ]);
    }
    #[Route('/cours4', name: 'app_cours_index4', methods: ['GET'])]
    public function index4(
        Request $request,
        CoursRepository $coursRepository,
        MatiereRepository $matiereRepository,
        UserRepository $userRepository
    ): Response {
        $session = $request->getSession();
        $userId = $session->get('id'); // Récupère l'ID de l'utilisateur depuis la session
    
        if (!$userId) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }
    
        $user = $userRepository->find($userId);
     
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }
    
        // Récupère les cours où l'utilisateur est inscrit
        $cours = $coursRepository->createQueryBuilder('c')
            ->innerJoin('c.etudiantsfavoris', 'e') // Joindre la relation "etudiants"
            ->where('e.id = :userId') // Filtrer par l'ID de l'utilisateur
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    
        return $this->render('cours/index1.html.twig', [
            'cours' => $cours,
            'matieres' => $matiereRepository->findAll(),
        ]);
    }
    
#[Route('/filter2', name: 'app_filter2', methods: ['GET'])]
public function filter2(
    Request $request,
    CoursRepository $coursRepository,
    MatiereRepository $matiereRepository,
    UserRepository $userRepository
): Response {
    $session = $request->getSession();
    $userId = $session->get('id'); // Récupère l'ID de l'utilisateur connecté

    if (!$userId) {
        throw $this->createAccessDeniedException('Utilisateur non connecté.');
    }

    $user = $userRepository->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur introuvable.');
    }

    // Filtre pour la matière
    $matiereId = $request->query->get('matiere');

    // Récupération des cours auxquels l'utilisateur est inscrit, avec un filtre par matière (si fourni)
    $queryBuilder = $coursRepository->createQueryBuilder('c')
        ->innerJoin('c.etudiantsfavoris', 'e') // Joindre la relation "etudiants"
        ->where('e.id = :userId') // Filtrer par l'utilisateur connecté
        ->setParameter('userId', $userId);

    if ($matiereId) {
        $queryBuilder->andWhere('c.matiere = :matiereId') // Ajouter le filtre par matière
            ->setParameter('matiereId', $matiereId);
    }

    $cours = $queryBuilder->getQuery()->getResult();

    return $this->render('cours/index1.html.twig', [
        'cours' => $cours,
        'matieres' => $matiereRepository->findAll(),
    ]);
}
      // etudiant
      #[Route('/filter', name: 'app_cours_filter', methods: ['GET'])]
      public function filter(Request $request, CoursRepository $coursRepository, MatiereRepository $matiereRepository): Response
      {
          $matiereId = $request->query->get('matiere');
          $cours = $matiereId ? $coursRepository->findBy(['matiere' => $matiereId]) : $coursRepository->findAll();
  
          return $this->render('cours/index.html.twig', [
              'cours' => $cours,
              'matieres' => $matiereRepository->findAll(),
          ]);
      }
      #[Route('/filter1', name: 'app_cours_filter1', methods: ['GET'])]
public function filter1(
    Request $request,
    CoursRepository $coursRepository,
    MatiereRepository $matiereRepository,
    UserRepository $userRepository
): Response {
    $session = $request->getSession();
    $userId = $session->get('id'); // Récupère l'ID de l'utilisateur connecté

    if (!$userId) {
        throw $this->createAccessDeniedException('Utilisateur non connecté.');
    }

    $user = $userRepository->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur introuvable.');
    }

    // Filtre pour la matière
    $matiereId = $request->query->get('matiere');

    // Récupération des cours auxquels l'utilisateur est inscrit, avec un filtre par matière (si fourni)
    $queryBuilder = $coursRepository->createQueryBuilder('c')
        ->innerJoin('c.etudiants', 'e') // Joindre la relation "etudiants"
        ->where('e.id = :userId') // Filtrer par l'utilisateur connecté
        ->setParameter('userId', $userId);

    if ($matiereId) {
        $queryBuilder->andWhere('c.matiere = :matiereId') // Ajouter le filtre par matière
            ->setParameter('matiereId', $matiereId);
    }

    $cours = $queryBuilder->getQuery()->getResult();

    return $this->render('cours/index3.html.twig', [
        'cours' => $cours,
        'matieres' => $matiereRepository->findAll(),
    ]);
}

      // enseignant
    #[Route('/coursenseignant',name: 'coursEnseignant', methods: ['GET'])]
    public function index1(CoursRepository $coursRepository,Request $request, EntityManagerInterface $entityManager): Response
    { $session = $request->getSession();
        $userId = $session->get('id');
        $enseignant = $entityManager->getRepository(User::class)->find($userId);

        return $this->render('cours/coursenseignant.html.twig', [
            'cours' => $coursRepository->findByEnseignant($enseignant),
            
        ]);
    }
    
    #[Route('/votre-route-confirmation/{id}', name: 'route2')]
    public function confirmationAction(int $id, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Récupération de l'utilisateur
        $userId = $request->getSession()->get('id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('login_route');
        }
    
        // Récupération du cours
        $cour = $entityManager->getRepository(Cours::class)->find($id);
        
        if (!$cour) {
            $this->addFlash('error', 'Cours non trouvé');
            return $this->redirectToRoute('cours_list_route');
        }
    
        try {
            // Ajout de l'étudiant
            $cour->addEtudiant($user);
            
            // Pas besoin de persist() pour les entités existantes
            $entityManager->flush();
    
            // Envoi de l'email de confirmation
            $emailAddress = $user->getEmail();
            $email = (new Email())
    ->from('projetknowlity@gmail.com')
    ->to($emailAddress)
    ->subject('Confirmation of Course Enrollment')
    ->text('You have successfully enrolled in the course: "' . $cour->getTitle() . '". Thank you for joining us!')
    ->html('<p>You have successfully enrolled in the course: <strong>"' . htmlspecialchars($cour->getTitle(), ENT_QUOTES) . '"</strong>. Thank you for joining us!</p>');
            // Envoyer l'email
            $mailer->send($email);
    
            $this->addFlash('success', 'Inscription réussie et email envoyé !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'inscription : '.$e->getMessage());
        }
    
        return $this->redirectToRoute('app_cours_show', [
            'id' => $cour->getId()
        ], Response::HTTP_SEE_OTHER);
    }
    
#[Route('/generate-pdf/{userId}/{coursId}', name: 'generate_pdf1')]
    public function generatePdf(
        int $userId, 
        int $coursId, 
        PdfGenerator $pdfGenerator, EntityManagerInterface $entityManager
    ): Response {
        // Récupérez vos entités (exemple avec Doctrine)
        $user = $entityManager->getRepository(User::class)->find($userId);
        $cours = $entityManager->getRepository(Cours::class)->find($coursId);
        if (!$user || !$cours) {
            throw $this->createNotFoundException('Utilisateur ou cours non trouvé');
        }

        // Génération du PDF
        $pdfContent = $pdfGenerator->generateRegistrationPdf($user, $cours);

        // Réponse avec le PDF
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="confirmation_inscription.pdf"'
            ]
        );
    }
    // enseignant
    #[Route('/cours/{id}/stats', name: 'app_cours_stats', methods: ['GET'])]
    public function stats(Cours $cours): Response
    {
        return $this->render('cours/stats.html.twig', [
            'cours' => $cours,
        ]);
    }
     // enseignant
     #[Route('/enseignant/{id}', name: 'app_cours', methods: ['GET'])]     
     public function show1(int $id, Request $request, EntityManagerInterface $entityManager): Response
     {
         $cour = $entityManager->getRepository(Cours::class)->find($id);
     
         if (!$cour) {
             throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
         }
     
         $session = $request->getSession();
         $userId = $session->get('id');
     
         if ($cour->getEnseignant()->getId() === $userId) {
             return $this->render('cours/showPourEnseignant.html.twig', [
                 'cour' => $cour,
             ]);
         } else {
             return $this->redirectToRoute('coursEnseignant');
         }
     }
     
     
    // etudiant
    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {

        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

// enseignant
#[Route('/new/{id}', name: 'app_cours_new', methods: ['GET', 'POST'])]
public function new(
    $id,
    Request $request,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory,
    #[Autowire('%env(FACEBOOK_PAGE_ID)%')] string $facebookPageId,
    #[Autowire('%env(FACEBOOK_ACCESS_TOKEN)%')] string $accessToken
): Response {
    $session = $request->getSession();
    $userId = $session->get('id');

    $enseignant = $entityManager->getRepository(User::class)->find($userId);
    $cour = new Cours();
    $cour->setEnseignant($enseignant);
    $form = $this->createForm(CoursType::class, $cour);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $brochureFile = $form->get('brochure')->getData();

        if ($brochureFile) {
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

            try {
                $brochureFile->move($brochuresDirectory, $newFilename);
                $brochurePath = $brochuresDirectory . '/' . $newFilename;
                $this->addWatermark($brochurePath, $brochuresDirectory . '/watermark.png');
            } catch (FileException $e) {
                // Gestion d'erreur
            }

            $cour->setUrlImage($newFilename);
        }

        $entityManager->persist($cour);
        $entityManager->flush();

        // Partage sur Facebook
        try {
            $client = HttpClient::create();
            
            $postMessage = sprintf(
                "🎓 Nouveau cours disponible !\n\n".
                "📚 Titre : %s\n".
                "📝 Description : %s\n".
                "💵 Prix : %s\n".
                "🌐 Langue : %s\n\n".
                "👉 Découvrez-le maintenant : %s",
                $cour->getTitle(),
                $cour->getDescription(),
                $cour->getPrix() > 0 ? $cour->getPrix().' DT' : 'Gratuit',
                strtoupper($cour->getLangue()),
                $this->generateUrl('app_cours_show', ['id' => $cour->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            );

            $response = $client->request('POST', "https://graph.facebook.com/v22.0/{$facebookPageId}/feed", [
                'query' => [
                    'message' => $postMessage,
                    'access_token' => $accessToken
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode !== 200 || isset($content['error'])) {
                $this->addFlash('warning', 'Le cours a été créé mais le partage Facebook a échoué');
            } else {
                $this->addFlash('success', 'Le cours a été créé et partagé sur Facebook !');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du partage Facebook : '.$e->getMessage());
        }

        return $this->redirectToRoute('app_chapitre_new', ['id' => $cour->getId()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('cours/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

/**
 * Ajouter un watermark à une image.
 */
private function addWatermark(string $imagePath, string $watermarkPath): void
{
    $imagine = new \Imagine\Gd\Imagine();

    // Load the main image and the watermark
    $image = $imagine->open($imagePath);
    $watermark = $imagine->open($watermarkPath);

    // Resize the watermark to make it smaller
    $scaledWatermark = $watermark->resize(
        $watermark->getSize()->scale(0.2) // Scale to 50% of the original size
    );

    // Get the sizes of the main image and scaled watermark
    $size = $image->getSize();
    $wSize = $scaledWatermark->getSize();

    // Calculate the position of the watermark (bottom-right, closer to the edge)
    $bottomRight = new \Imagine\Image\Point(
        $size->getWidth() - $wSize->getWidth() - 2, // 5px margin from the right
        $size->getHeight() - $wSize->getHeight() - 2 // 5px margin from the bottom
    );

    // Apply the watermark
    $image->paste($scaledWatermark, $bottomRight, 50);
    // Save the image with the watermark
    $image->save($imagePath);
}


    // enseignant
    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Cours $cour,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory
    ): Response {
        $userId = $request->getSession()->get('id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        $enseignant=$cour->getEnseignant();
        if($user->getId()==$enseignant->getId() || $user->getRoles()=="Admin" ){
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('brochure')->getData();
    
            // Gérer l'upload du fichier si un nouveau fichier est fourni
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
    
                // Déplacer le fichier dans le répertoire de stockage
                try {
                    $brochureFile->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // Gérer l'exception en cas d'erreur lors de l'upload
                    $this->addFlash('error', 'Une erreur s\'est produite lors de l\'upload du fichier.');
                    return $this->redirectToRoute('app_cours_edit', ['id' => $cour->getId()]);
                }
    
                // Supprimer l'ancien fichier s'il existe
                $oldFilename = $cour->getUrlImage();
                if ($oldFilename && file_exists($brochuresDirectory . '/' . $oldFilename)) {
                    unlink($brochuresDirectory . '/' . $oldFilename);
                }
    
                // Mettre à jour le nom du fichier dans l'entité
                $cour->setUrlImage($newFilename);
            }
    
            // Enregistrer les modifications en base de données
            $entityManager->flush();
    
            return $this->redirectToRoute('app_cours', ['id' => $cour->getId()], Response::HTTP_SEE_OTHER);
        }}
        else{
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

#[Route('/{id}/favorite', name: 'app_cours_toggle_favorite', methods: ['POST'])]
public function toggleFavorite(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
{
    $userId = $request->getSession()->get('id');
    $user = $entityManager->getRepository(User::class)->find($userId);

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    if ($cour->isUserFavorite($userId)) {
        $cour->removeEtudiantfavoris($user);
    } else {
        $cour->addEtudiantfavoris($user);
    }

    $entityManager->flush();

    return $this->redirectToRoute('app_cours_show', ['id' => $cour->getId()]);
}
// enseignant
    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('coursEnseignant', ['id'=>1], Response::HTTP_SEE_OTHER);
    }
}
