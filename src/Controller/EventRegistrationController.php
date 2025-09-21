<?php

namespace App\Controller;

use App\Entity\EventRegistration;
use App\Form\EventRegistrationType;
use App\Repository\EventRegistrationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RequestStack;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;
use App\Entity\UserEventPreference;

#[Route('/event/registration')]
final class EventRegistrationController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    #[Route(name: 'app_event_registration_index', methods: ['GET'])]
    public function index(EventRegistrationRepository $eventRegistrationRepository): Response
    {
        return $this->render('event_registration/index.html.twig', [
            'event_registrations' => $eventRegistrationRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_event_registration_new', methods: ['GET', 'POST'])]
    public function register(int $id, EntityManagerInterface $em, UserRepository $userRepository, Request $request): Response 
    {
        $session = $this->requestStack->getSession();
        $user_id = $session->get('id');

        $event = $em->getRepository(Events::class)->find($id);
        $user = $userRepository->findOneById($user_id); 

        if (!$event || !$user) {
            throw $this->createNotFoundException('Event or User not found.');
        }

        $existingRegistration = $em->getRepository(EventRegistration::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($existingRegistration) {
            $this->addFlash('info', 'You are already registered for this event.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $reservation = new EventRegistration();
        $form = $this->createForm(EventRegistrationType::class, $reservation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Associate entities
            $reservation->setEvent($event);
            $reservation->setUser($user);

            $em->persist($reservation);

            // ✅ Store or update user preference
            $preference = $em->getRepository(UserEventPreference::class)->findOneBy([
                'user' => $user,
                'category' => $event->getCategory() // Assuming 'type' is the category
            ]);

            if ($preference) {
                $preference->setPreferenceScore($preference->getPreferenceScore() + 1);
            } else {
                $preference = new UserEventPreference();
                $preference->setUser($user);
                $preference->setEvent($event);
                $preference->setCategory($event->getCategory());
                $preference->setPreferenceScore(1);
                $em->persist($preference);
            }

            $em->flush();

            $this->addFlash('success', 'Reservation successful!');
            return $this->redirectToRoute('app_events_index');
        }

        return $this->render('event_registration/new.html.twig', [
            'event' => $event,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_event_registration_show', methods: ['GET'])]
    public function show(EventRegistration $eventRegistration): Response
    {
        return $this->render('admin_event/show.html.twig', [
            'event_registration' => $eventRegistration,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_registration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventRegistration $eventRegistration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventRegistrationType::class, $eventRegistration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_my_registration', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event_registration/edit.html.twig', [
            'event_registration' => $eventRegistration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_registration_delete', methods: ['POST'])]
    public function delete(Request $request, EventRegistration $eventRegistration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$eventRegistration->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($eventRegistration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_my_registration', [], Response::HTTP_SEE_OTHER);
    }


        public function registerUserForEvent(EntityManagerInterface $em, User $user, Events $event): Response
    {
        $registration = new EventRegistration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setRegistrationDate(new \DateTime());
        $registration->setStatus('confirmed');

        $em->persist($registration);
        $em->flush();

        return new Response('User registered successfully!');
    }

    #[Route('/{id}/update-status', name: 'update_event_registration_status', methods: ['POST'])]
    public function updateStatus(Request $request,MailerInterface $mailer, EventRegistration $eventRegistration, EntityManagerInterface $entityManager): Response
    {
        // Get the new status from the form submission
        $newStatus = $request->get('status');
        
        // Get the associated Event
        $event = $eventRegistration->getEvent();
        $user = $eventRegistration->getUser();
        // Get the current status of the registration
        $currentStatus = $eventRegistration->getStatus();
        
        // If the status is changed, update the status and adjust seats available
        if ($newStatus) {
            $eventRegistration->setStatus($newStatus);
    
            // Handle seat adjustments based on status change
            // If status is being changed to 'Confirmed' from another status (Pending, Canceled)
            if ($newStatus === 'Confirmed' && $currentStatus !== 'Confirmed') {
                // Decrease the available seats when status is set to confirmed
                $seatsAvailable = $event->getSeatsAvailable();
                if ($seatsAvailable > $eventRegistration->getPlacesReserved()) {
                    $event->setSeatsAvailable($seatsAvailable - $eventRegistration->getPlacesReserved());
                    $data = "Event Registration Details:\n";
                    $data .= "🔹 ID: " . $eventRegistration->getId() . "\n";
                    $data .= "🔹 Name: " . $eventRegistration->getName() . "\n";
                    $data .= "🔹 Place Reserved: " . $eventRegistration->getPlacesReserved() . "\n";
                    $data .= "🔹 Coming From: " . $eventRegistration->getComingFrom() . "\n";
                    $data .= "🔹 Event: " . $eventRegistration->getEvent()->getTitle() . " (ID: " . $eventRegistration->getEvent()->getId() . ")\n";
                    $data .= "🔹 User: " . $eventRegistration->getUser()->getRoles() . " (ID: " . $eventRegistration->getUser()->getId() . ")\n";
                
                    $builder = new Builder(
                        writer: new PngWriter(), // You can choose PngWriter or SvgWriter
                        writerOptions: [],
                        validateResult: false,
                        data: $data, // Custom QR code contents
                        encoding: new Encoding('UTF-8'),
                        errorCorrectionLevel: ErrorCorrectionLevel::High,
                        size: 350,
                        margin: 10,
                        roundBlockSizeMode: RoundBlockSizeMode::Margin,
                        logoPath: __DIR__.'/../../public/145.png',
                        logoResizeToWidth: 50,
                        logoPunchoutBackground: true,
                        labelText: 'Event Registration QR Code',
                        labelFont: new OpenSans(20),
                        labelAlignment: LabelAlignment::Center
                    );
                    $result = $builder->build();
                    // Generate the QR code
                    $directory = realpath('public/uploads/qr-codes');
                    if (!$directory) {
                        mkdir('public/uploads/qr-codes', 0775, true); // Create directory if not exists
                    }

                    $qrCodePath = $directory . '/qr-code.png';
                    $result->saveToFile($qrCodePath);

                
                    // Send the email with the png QR code attached
                    $email = $user->getEmail();
                    $email = (new Email())
                        ->from('projetknowlity@gmail.com')
                        ->to($email)
                        ->subject('Event Registration')
                        ->text('Event Registration to ' . $event->getTitle())
                        ->html('<p>Event Registration is successfully completed. This is a QR code for the confirmation:</p><img src="cid:qr-code-image" />');
                    
                    // Attach the png QR code with the correct MIME type and Content-ID
                    $email->attachFromPath($qrCodePath, 'qr-code.png', 'image/png+xml')
                        ->getHeaders()->addTextHeader('Content-ID', '<qr-code-image>'); // Ensure correct Content-ID format
                
                    // Send the email
                    $mailer->send($email);
                }
                
                 else {
                    $this->addFlash('error', 'No seats available to confirm the registration!');
                    return $this->redirectToRoute('app_event_registration_show', ['id' => $eventRegistration->getId()]);
                }
            }
    
            // If status was 'Confirmed' and is now 'Pending' or 'Canceled', increment the seats
            if ($currentStatus === 'Confirmed' && ($newStatus === 'Pending' || $newStatus === 'Canceled')) {
                $seatsAvailable = $event->getSeatsAvailable();
                $event->setSeatsAvailable($seatsAvailable + $eventRegistration->getPlacesReserved()); // Increase by 1
            }
    
            // Persist the changes to the database
            $entityManager->flush();
            
            // Add a flash message for feedback
            $this->addFlash('success', 'Event registration status updated!');
        } else {
            $this->addFlash('error', 'Failed to update the status.');
        }
    
        // Redirect back to the event registration page
        return $this->redirectToRoute('app_event_registration_show', ['id' => $eventRegistration->getId()]);
    }
    


}
