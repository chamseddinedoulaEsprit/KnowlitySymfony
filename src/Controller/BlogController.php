<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Form\BlogSearchType;
use App\Entity\Like;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\BlogRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_blog_list');
    }

    #[Route('/list', name: 'app_blog_list')]
    public function list(Request $request, BlogRepository $blogRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('q');
        $queryBuilder = $blogRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        if ($query) {
            $queryBuilder
                ->where('b.title LIKE :query OR b.content LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('blog/index.html.twig', [
            'blogs' => $pagination,
        ]);
    }

    #[Route('/list/user', name: 'app_blog_list_user', methods: ['GET'])]
    public function listUser(Request $request, BlogRepository $blogRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('q');
        $queryBuilder = $blogRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        if ($query) {
            $queryBuilder
                ->where('b.title LIKE :query OR b.content LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('blog/index2.html.twig', [
            'blogs' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imageDirectory
    ): Response {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement de l'image
            $imageFile = $form->get('blogImage')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirectToRoute('app_blog_new');
                }
    
                // Définit le chemin de l'image dans l'entité
                $blog->setBlogImage($newFilename);
            }
    
            $blog->setCreatedAt(new \DateTime());
            $entityManager->persist($blog);
            $entityManager->flush();
    
            $this->addFlash('success', 'Blog créé avec succès !');
            return $this->redirectToRoute('app_blog_show2', ['id' => $blog->getId()]);
        }
    
        return $this->render('blog/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, BlogRepository $blogRepository): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = [];
            
            if ($form->get('title')->getData() === '') {
                $errors['title'] = 'Le titre est obligatoire';
            } elseif (strlen($form->get('title')->getData()) < 3) {
                $errors['title'] = 'Le titre doit contenir au moins 3 caractères';
            }
            
            if ($form->get('content')->getData() === '') {
                $errors['content'] = 'Le contenu est obligatoire';
            } elseif (strlen($form->get('content')->getData()) < 10) {
                $errors['content'] = 'Le contenu doit contenir au moins 10 caractères';
            }
            
            if ($form->get('creatorName')->getData() === '') {
                $errors['creatorName'] = 'Le nom du créateur est obligatoire';
            }
            
            $userImage = $form->get('userImage')->getData();
            if ($userImage !== '' && !filter_var($userImage, FILTER_VALIDATE_URL)) {
                $errors['userImage'] = 'L\'URL de l\'image de profil n\'est pas valide';
            }
            
            $blogImage = $form->get('blogImage')->getData();
            if ($blogImage !== '' && !filter_var($blogImage, FILTER_VALIDATE_URL)) {
                $errors['blogImage'] = 'L\'URL de l\'image du blog n\'est pas valide';
            }

            if (empty($errors) && $form->isValid()) {
                $blogRepository->save($blog, true);
                return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('blog/edit.html.twig', [
                'blog' => $blog,
                'form' => $form,
                'errors' => $errors
            ]);
        }

        return $this->renderForm('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
            'errors' => []
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('app_blog_index');
    }

    #[Route('/{id}/show2', name: 'app_blog_show2', methods: ['GET'])]
    public function show2(Blog $blog): Response
    {
        return $this->render('blog/show2.html.twig', [
            'blog' => $blog,
        ]);
    }

   

    #[Route('/blog/{id}/comment', name: 'app_blog_comment', methods: ['POST'])]
    public function comment(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $content = $request->request->get('content');
        $username = $request->request->get('username');

        if (!empty($content) && !empty($username)) {
            $comment = new Comment();
            $comment->setBlog($blog);
            $comment->setUsername($username);
            $comment->setContent($content);
            $comment->setCreatedAt(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès !');
        } else {
            $this->addFlash('error', 'Le nom et le commentaire sont requis.');
        }

        return $this->redirectToRoute('app_blog_show2', ['id' => $blog->getId()]);
    }

    #[Route('/comment/{id}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $content = $data['content'] ?? null;
            
            if (!$content) {
                return $this->json(['error' => 'Le contenu du commentaire ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
            }

            $comment->setContent($content);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de la modification du commentaire'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete-comment', $request->headers->get('X-CSRF-TOKEN'))) {
                return $this->json(['error' => 'Token de sécurité invalide'], Response::HTTP_FORBIDDEN);
            }

            $entityManager->remove($comment);
            $entityManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de la suppression du commentaire'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/blog/{id}/like', name: 'app_blog_toggle_like', methods: ['POST'])]
public function toggleLike(
    Request $request,
    Blog $blog,
    EntityManagerInterface $entityManager
): Response {
    // Récupérer l'ID de l'utilisateur depuis la session
    $userId = $request->getSession()->get('id');

    // Vérifier si l'utilisateur est connecté
    if (!$userId) {
        return $this->redirectToRoute('app_login');
    }

    // Récupérer l'utilisateur à partir de l'ID
    $user = $entityManager->getRepository(User::class)->find($userId);

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Ajouter ou retirer le like
    if ($blog->isLikedByUser($user->getId())) {
        $blog->removeLike($user);
    } else {
        $blog->addLike($user);
    }

    // Sauvegarder les modifications dans la base de données
    $entityManager->persist($blog);
    $entityManager->flush();

    // Rediriger vers la page d'affichage du blog
    return $this->redirectToRoute('app_blog_show2', ['id' => $blog->getId()]);
}

}