<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function testEmail(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
                ->from('test@example.com')
                ->to('your.email@example.com')  // Remplacez par votre adresse email
                ->subject('Test Email from Symfony Mailer')
                ->text('Ceci est un email de test envoyé depuis l\'application Symfony.')
                ->html('<p>Ceci est un email de test envoyé depuis l\'application Symfony.</p>');

            $mailer->send($email);

            return new Response('Email envoyé avec succès !');
        } catch (\Exception $e) {
            return new Response('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage(), 500);
        }
    }
}
