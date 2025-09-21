<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Psr\Log\LoggerInterface;

class ProfanityCheckerService
{
    private $httpClient;
    private $logger;

    // Liste des mots inappropriés en anglais
    private $englishProfanityList = [
        // Insultes courantes
        'fuck', 'shit', 'bitch', 'ass', 'damn', 'piss', 'cunt', 'cock', 'pussy', 'dick',
        'bastard', 'asshole', 'motherfucker', 'fucker', 'bullshit', 'horseshit',
        
        // Insultes raciales et discriminatoires
        'nigger', 'nigga', 'faggot', 'dyke', 'retard', 'spic', 'wetback', 'kike',
        
        // Variations et combinaisons
        'fck', 'fuk', 'fuking', 'fcking', 'sh1t', 'b1tch', 'a$$', 'azzhole',
        'motherf*cker', 'f*ck', 'sh*t', 'b*tch', 'p*ssy', 'd*ck'
    ];

    // Liste des mots inappropriés en français
    private $frenchProfanityList = [
        // Insultes courantes
        'merde', 'putain', 'connard', 'salaud', 'connasse', 'salope', 'pute',
        'enculé', 'bite', 'couille', 'chier', 'foutre', 'cul', 'nique',
        
        // Variations et combinaisons
        'm*rde', 'put*in', 'conn*rd', 'sal*pe', 'enc*lé', 'b*te',
        'merder', 'emmerde', 'emmerder', 'chiant', 'chier', 'chieur',
        
        // Insultes raciales et discriminatoires
        'négro', 'bougnoul', 'youpin', 'bougnoule', 'bamboula', 'chinetoque'
    ];

    // Liste des mots inappropriés en arabe
    private $arabicProfanityList = [
        'zab', 'zeb', 'zebi', 'zbel', 'kahba', 'kahbe', 'kahba', 'kahbe',
        'nayek', 'nik', 'nique', 'niquer', 'nikek', 'neek', 'nayak',
        'zok', 'zouk', 'zokk', 'zoukk', 'zokkom', 'zokkomak'
    ];

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function containsProfanity(string $text): bool
    {
        $lowercaseText = strtolower($text);

        // Vérifier dans toutes les listes de mots
        $allProfanityLists = array_merge(
            $this->englishProfanityList,
            $this->frenchProfanityList,
            $this->arabicProfanityList
        );

        foreach ($allProfanityLists as $word) {
            if (str_contains($lowercaseText, $word)) {
                $this->logger->info('Profanity detected using local list', [
                    'text' => $text,
                    'word' => $word
                ]);
                return true;
            }
        }

        // Vérifier les motifs plus complexes (avec des espaces ou des caractères spéciaux)
        $patterns = [
            '/n+[i1l|]+[gq]+[e3]+r+/i',    // Variations de "nigger"
            '/f+[u\*]+c+k+/i',             // Variations de "fuck"
            '/b+[i1\*]+t+c+h+/i',          // Variations de "bitch"
            '/m+[e3\*]+r+d+[e3]+/i',       // Variations de "merde"
            '/p+[u\*]+t+[e3]+/i',          // Variations de "pute"
            '/z+[e3\*]+b+[i1]+/i'          // Variations de "zebi"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $lowercaseText)) {
                $this->logger->info('Profanity detected using pattern matching', [
                    'text' => $text,
                    'pattern' => $pattern
                ]);
                return true;
            }
        }

        // Essayer avec l'API externe
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://www.purgomalum.com/service/containsprofanity?text=' . urlencode($text)
            );

            $result = $response->getContent() === 'true';
            if ($result) {
                $this->logger->info('Profanity detected using external API', [
                    'text' => $text
                ]);
            }
            return $result;
        } catch (ExceptionInterface $e) {
            $this->logger->error('Error checking profanity with external API', [
                'error' => $e->getMessage(),
                'text' => $text
            ]);
            // En cas d'erreur avec l'API, on se base uniquement sur la liste locale
            return false;
        }
    }
}