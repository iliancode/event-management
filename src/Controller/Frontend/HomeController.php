<?php

namespace App\Controller\Frontend;

use App\Constants\RouteConstants;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    #[Route('', name: RouteConstants::ROUTE_HOME)]
    public function index(): Response
    {
        $events = $this->eventRepository->findNextEvents(3);

        return $this->render('frontend/home/index.html.twig', [
            'events' => $events,
        ]);
    }
}
