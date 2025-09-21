<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Evaluation;
use App\Entity\User;
use App\Entity\Question;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailService
{
    private $mailer;
    private $sender;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->sender = $_ENV['MAILER_FROM_ADDRESS'] ?? 'projetknowlity@gmail.com';
        $this->logger = $logger;
    }

    /**
     * Envoie un email de manière sécurisée avec gestion des erreurs
     */
    private function sendEmail(Email $email): bool
    {
        try {
            $this->mailer->send($email);
            $this->logger->info('Email envoyé avec succès', [
                'to' => $email->getTo(),
                'subject' => $email->getSubject()
            ]);
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email', [
                'error' => $e->getMessage(),
                'to' => $email->getTo(),
                'subject' => $email->getSubject()
            ]);
            return false;
        }
    }

    public function sendEvaluationSubmittedToStudent(User $student, Evaluation $evaluation): bool
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($student->getEmail())
            ->subject('Évaluation soumise avec succès')
            ->html(
                "<h1>Votre évaluation a été enregistrée</h1>" .
                "<p>Bonjour {$student->getPrenom()} {$student->getNom()},</p>" .
                "<p>Nous confirmons que votre évaluation <strong>{$evaluation->getTitle()}</strong> a été soumise avec succès.</p>" .
                "<p>Vous serez notifié par email lorsque votre enseignant aura terminé la correction.</p>" .
                "<p>Cordialement,<br>L'équipe Knowlity</p>"
            );

        return $this->sendEmail($email);
    }

    public function sendEvaluationGradedToStudent(User $student, Evaluation $evaluation): bool
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($student->getEmail())
            ->subject('Votre évaluation a été corrigée')
            ->html(
                "<h1>Évaluation corrigée</h1>" .
                "<p>Bonjour {$student->getPrenom()} {$student->getNom()},</p>" .
                "<p>Votre évaluation <strong>{$evaluation->getTitle()}</strong> a été corrigée par votre enseignant.</p>" .
                "<p>Vous pouvez maintenant consulter vos résultats sur la plateforme.</p>" .
                "<p>Cordialement,<br>L'équipe Knowlity</p>"
            );

        return $this->sendEmail($email);
    }

    public function sendEvaluationCreatedToTeacher(User $teacher, Evaluation $evaluation): bool
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($teacher->getEmail())
            ->subject('Confirmation de création d\'évaluation')
            ->html(
                "<h1>Évaluation créée avec succès</h1>" .
                "<p>Bonjour {$teacher->getPrenom()} {$teacher->getNom()},</p>" .
                "<p>Nous confirmons que votre évaluation <strong>{$evaluation->getTitle()}</strong> a été créée avec succès.</p>" .
                "<p>Les étudiants peuvent maintenant y accéder selon les paramètres que vous avez définis.</p>" .
                "<p>Cordialement,<br>L'équipe Knowlity</p>"
            );

        return $this->sendEmail($email);
    }

    public function sendInappropriateContentAlert(
        User $student,
        Evaluation $evaluation,
        Question $question,
        string $reponseText,
        User $teacher
    ): bool {
        $email = (new Email())
            ->from($this->sender)
            ->to($teacher->getEmail())
            ->subject('Contenu inapproprié détecté dans une réponse')
            ->html(sprintf(
                "<h1>Contenu inapproprié détecté</h1>" .
                "<p>Un contenu inapproprié a été détecté dans une réponse :</p>" .
                "<ul>" .
                "<li><strong>Évaluation :</strong> %s</li>" .
                "<li><strong>Question :</strong> %s</li>" .
                "<li><strong>Étudiant :</strong> %s (%s)</li>" .
                "<li><strong>Date :</strong> %s</li>" .
                "</ul>" .
                "<p><strong>Réponse :</strong><br>%s</p>" .
                "<p>Veuillez vérifier le contenu et prendre les mesures appropriées.</p>" .
                "<p>Cordialement,<br>L'équipe Knowlity</p>",
                $evaluation->getTitle(),
                $question->getEnonce(),
                $student->getPrenom() . ' ' . $student->getNom(),
                $student->getEmail(),
                (new \DateTime())->format('d/m/Y H:i:s'),
                nl2br(htmlspecialchars($reponseText))
            ));

        return $this->sendEmail($email);
    }

    public function sendInappropriateContentWarningToStudent(
        User $student,
        Evaluation $evaluation,
        Question $question,
        string $warningMessage
    ): bool {
        $email = (new Email())
            ->from($this->sender)
            ->to($student->getEmail())
            ->subject('Avertissement : Contenu inapproprié dans votre réponse')
            ->html(sprintf(
                "<h1>Avertissement : Contenu inapproprié</h1>" .
                "<p>Bonjour %s %s,</p>" .
                "<p>Un contenu inapproprié a été détecté dans votre réponse pour :</p>" .
                "<ul>" .
                "<li><strong>Évaluation :</strong> %s</li>" .
                "<li><strong>Question :</strong> %s</li>" .
                "</ul>" .
                "<p><strong>Message de l'enseignant :</strong><br>%s</p>" .
                "<p>Votre note pour cette question a été mise à 0. Veuillez respecter les règles de bienséance dans vos futures réponses.</p>" .
                "<p>Cordialement,<br>L'équipe Knowlity</p>",
                $student->getPrenom(),
                $student->getNom(),
                $evaluation->getTitle(),
                $question->getEnonce(),
                nl2br(htmlspecialchars($warningMessage))
            ));

        return $this->sendEmail($email);
    }
}