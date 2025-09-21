<?php

namespace App\Controller;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;   
#[Route('/categorie')]
final class CategorieController extends AbstractController
{
    #[Route(name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

   

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,
                        #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory,
                        FilterManager $filterManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement de l'image téléchargée (brochure)
            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.jpg'; // Force output to JPEG
            
                // Convert to a supported format using Imagine
                $imagine = new Imagine();
                $image = $imagine->open($brochureFile->getPathname());
                $imageContent = $image->get('jpeg', ['quality' => 80]); // Convert to JPEG
            
                // Create Binary with correct MIME type
                $binary = new Binary($imageContent, 'image/jpeg');
            
                // Apply filter
                $filteredImage = $filterManager->applyFilter($binary, 'my_filter');
            
                // Save the filtered image
                try {
                    file_put_contents(
                        $brochuresDirectory . '/' . $newFilename,
                        $filteredImage->getContent()
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                }
    
                // Mettez à jour l'entité avec le nouveau nom de fichier de l'image
                $categorie->setIcone($newFilename);
            }
    
            // Enregistrer l'entité dans la base de données
            $entityManager->persist($categorie);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_categorie_index', ['id' => $categorie->getId()], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory,
    FilterManager $filterManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.jpg'; // Force output to JPEG
            
                // Convert to a supported format using Imagine
                $imagine = new Imagine();
                $image = $imagine->open($brochureFile->getPathname());
                $imageContent = $image->get('jpeg', ['quality' => 80]); // Convert to JPEG
            
                // Create Binary with correct MIME type
                $binary = new Binary($imageContent, 'image/jpeg');
            
                // Apply filter
                $filteredImage = $filterManager->applyFilter($binary, 'my_filter');
            
                // Save the filtered image
                try {
                    file_put_contents(
                        $brochuresDirectory . '/' . $newFilename,
                        $filteredImage->getContent()
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                }
    
                // Mettez à jour l'entité avec le nouveau nom de fichier de l'image
                $categorie->setIcone($newFilename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
