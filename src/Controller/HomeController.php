<?php

namespace App\Controller;

use App\Entity\Home;
use App\Entity\Events;
use App\Entity\Cours;
use App\Entity\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use App\Repository\EventsRepository;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository,CoursRepository $coursRepository,UserRepository $userRepository): Response
    {
        return $this->render('home/front_home.html.twig', [
            'events'=>$eventsRepository->findAll(),
            'cours'=>$coursRepository->findAll(),
            'teachers'=>$userRepository->findEnseignant(),
        ]);
    }
}
