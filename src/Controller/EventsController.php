<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Events;
use App\Entity\User;
use App\Entity\Cours;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;
use App\Repository\EventRegistrationRepository;
use App\Entity\EventRegistration;
use Symfony\Component\HttpFoundation\RequestStack;
#[Route('/events')]
final class EventsController extends AbstractController{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository, UserRepository $userRepository): Response
    {
        $session = $this->requestStack->getSession();
        $user_id = $session->get('id');
        $user = $userRepository->findOneById($user_id);
        $recommendedEvents = $eventsRepository->findRecommendedEvents($user);   
        return $this->render('events/index.html.twig', [
            'events' => $eventsRepository->findAll(),
            'recommendation' => $recommendedEvents,
        ]);
    }

    #[Route("/my_events",name: 'app_events_my_events', methods: ['GET'])]
    public function my_events(EventsRepository $eventsRepository, UserRepository $userRepository,EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $session = $this->requestStack->getSession();
        $user_id = $session->get('id');
        $registrations=$eventRegistrationRepository->findByUserId($user_id);
        $events = [];
        foreach($registrations as $registration){
            $events[]=$registration->getEvent();
        }
        $user = $userRepository->findOneById($user_id);
        $recommendedEvents = $eventsRepository->findRecommendedEvents($user); 
        return $this->render('events/my_events.html.twig', [
            'events' => $events,
            'registrations'=>$registrations,
            'recommendation' => $recommendedEvents
        ]);
    }

    #[Route("/my_registration",name: 'app_events_my_registration', methods: ['GET'])]
    public function my_registration(EventsRepository $eventsRepository, UserRepository $userRepository,EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $session = $this->requestStack->getSession();
        $user_id = $session->get('id');
        $registrations=$eventRegistrationRepository->findByUserId($user_id);
        $events = [];
        foreach($registrations as $registration){
            $events[]=$registration->getEvent();
        }
        return $this->render('events/my_registrations.html.twig', [
            'events' => $events,
            'registrations'=>$registrations,
        ]);
    }

    #[Route('/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger, UserRepository $userRepository): Response
    {
        $session = $this->requestStack->getSession();
        $user_id = $session->get('id');
        $user = $userRepository->findOneById($user_id);
        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);
    
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the uploads directory
            $imageFile->move(
                $this->getParameter('event_images_directory'), // Define this parameter in config
                $newFilename
            );

            // Save only the file path
            $event->setImage($newFilename);
        }
            $event->setSeatsAvailable($form->get('max_participants')->getData());
            $event->setOrganizer($user);
            $event->setLongitude(10.180712940476518);
            $event->setLatitude(36.80197599768998);
    
            $entityManager->persist($event);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_events_index');
        }
    
        return $this->render('events/new.html.twig', [
            'event' => $event,
            'form' => $form,
            'user' => $user,
        ]);
    }
    

    #[Route('/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Events $event , UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneById(1);
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        

        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/event/{id}/register', name: 'event_register')]
    public function register(Events $event, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneById(2);

        // Check if the user is already registered
        $existingRegistration = $em->getRepository(EventRegistration::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($existingRegistration) {
            $this->addFlash('info', 'You are already registered for this event.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Create a new registration
        $registration = new EventRegistration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setRegistrationDate(new \DateTime());
        $registration->setStatus('confirmed');

        $em->persist($registration);
        $em->flush();

        $this->addFlash('success', 'You have successfully registered for the event!');
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'user' => $user,
        ]);
    }

    #[Route('/api/user/{id}/data', name: 'user_data', methods: ['GET'])]
    public function getUserData($id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Find the user
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Get the related courses
        $courses = $entityManager->getRepository(Cours::class)->findBy(['enseignant' => $user]);

        $eventsregistrations = $entityManager->getRepository(EventRegistration::class)->findBy(['user' => $user]);

        $events = [];
            foreach ($eventsregistrations as $registration) {
                $event = $registration->getEvent();
                if ($event !== null) {
                    $events[] = $event;
                }
            }


        // Convert data to an array
        $courseData = array_map(fn($course) => [
            'title' => $course->getTitle(),
            'price' => $course->getPrix(),
        ], $courses);

        $eventData = array_map(fn($event) => [
            'title' => $event->getTitle(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()->format('Y-m-d H:i:s')
        ], $events);

        return new JsonResponse([
            'username' => $user->getNom(),
            'role' => $user->getRoles(),
            'courses' => $courseData,
            'events' => $eventData
        ]);
    }
}
