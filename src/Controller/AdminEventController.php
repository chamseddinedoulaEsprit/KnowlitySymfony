<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use App\Repository\EventRegistrationRepository;
use App\Entity\EventRegistration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
final class AdminEventController extends AbstractController
{
    #[Route('/eventCharts', name: 'app_admin_event_charts')]
    public function eventCharts(EventsRepository $eventsRepository , EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $events = $eventsRepository->findAll();
        return $this->render('admin_event/eventCharts.html.twig', [
            'events' => $events,
            'events_registration' => $eventRegistrationRepository->findAll()
        ]);
    }


    #[Route('/event', name: 'app_admin_event')]
    public function index(EventsRepository $eventsRepository): Response
    {
        return $this->render('admin_event/event.html.twig', [
            'event' => $eventsRepository->findAll(),
        ]);
    }

    #[Route('/event/{id}', name: 'app_admin_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_event', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/event/{id}/edit', name: 'app_admin_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

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
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_event', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }


    #[Route('/registration',name: 'app_admin_event_registration_index', methods: ['GET'])]
    public function registration(EventRegistrationRepository $eventRegistrationRepository): Response
    {
        return $this->render('admin_event/registration.html.twig', [
            'event_registrations' => $eventRegistrationRepository->findAll(),
        ]);
    }


    #[Route('/events/search', name: 'event_search', methods: ['GET'])]
    public function search(Request $request, EventsRepository $eventRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        $events = $eventRepository->createQueryBuilder('e')
            ->where('e.title LIKE :query OR e.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $formattedEvents = array_map(function ($event) {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'image' => $event->getImage(),
                'description' => $event->getDescription(),
                'startDate' => $event->getStartDate()->format('Y-m-d H:i'),
                'endDate' => $event->getEndDate()->format('Y-m-d H:i'),
            ];
        }, $events);

        return $this->json($formattedEvents);
    }
}
