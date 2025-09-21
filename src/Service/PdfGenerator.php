<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class PdfGenerator
{
    private $twig;
    private $parameterBag;
    private $logger;

    public function __construct(Environment $twig, ParameterBagInterface $parameterBag, LoggerInterface $logger = null)
    {
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
    }

    public function generateRegistrationPdf($user, $cours): string
    {
        // Configuration des options de Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true); // For remote images
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('chroot', $this->parameterBag->get('kernel.project_dir')); // Restrict DomPDF to project directory

        // Define the base path for local assets
        $basePath = $this->parameterBag->get('kernel.project_dir') . '/public/';

        // Explicit image paths with file:// protocol for local files
        $logoPath = 'file://' . $basePath . '145.png';
        $signaturePath = 'file://' . $basePath . 'signature.png';

        // Debugging: Check if files exist and log issues
        if ($this->logger) {
            if (!file_exists(str_replace('file://', '', $logoPath))) {
                $this->logger->warning("Logo image not found at: $logoPath");
            }
            if (!file_exists(str_replace('file://', '', $signaturePath))) {
                $this->logger->warning("Signature image not found at: $signaturePath");
            }
        }

        // Render the Twig template
        $html = $this->twig->render('pdf/registration_confirmation.html.twig', [
            'user' => $user,
            'cours' => $cours,
            'app_name' => $this->parameterBag->get('app_name'),
            'logo_path' => $logoPath,
            'signature_path' => $signaturePath,
            'debug' => true, // Enable debug mode to show paths in PDF
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}