<?php

namespace App\Controller\Frontend;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\Event;
use App\Entity\EventParticipation;
use App\Entity\User;
use App\Form\EventFilterFormType;
use App\Form\EventFormType;
use App\Form\EventParticipationFormType;
use App\Repository\EventParticipationRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/events')]
class EventController extends AbstractController
{
    public function __construct
    (
        private readonly EventRepository $eventRepository,
        private readonly EventParticipationRepository $eventParticipationRepository,
        private readonly EntityManagerInterface $em,
        private readonly PaginatorInterface $paginator
    )
    {
    }

    private function checkEvent(?Event $event): void
    {
        if (!$event instanceof Event) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le event n\'existe pas');
            throw $this->createNotFoundException('Le event n\'existe pas');
        }
    }

    private function isUserInEvent(Event $event, ?User $user): bool
    {
        return $this->eventParticipationRepository->findOneBy(['event' => $event, 'user' => $user]) instanceof EventParticipation;
    }

    private function addUserToEvent(Event $event, ?User $user = null): void
    {
        $eventParticipation = new EventParticipation();
        $eventParticipation->setEvent($event);
        $eventParticipation->setUser($user ?? $this->getUser());

        $this->em->persist($eventParticipation);

        $event->addEventParticipation($eventParticipation);
    }

    private function addParticipantToEvent(Event $event, String $fullname): void
    {
        $eventParticipation = new EventParticipation();
        $eventParticipation->setEvent($event);
        $eventParticipation->setFullname($fullname);

        $this->em->persist($eventParticipation);

        $event->addEventParticipation($eventParticipation);
    }

    #[Route('', name: RouteConstants::ROUTE_EVENTS, methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1) < 1 ? 1 : $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10) < 1 ? 10 : $request->query->getInt('limit', 10);

        // Handle the form submission
        $filters = $this->createForm(EventFilterFormType::class, [
            'search' => $request->query->get('search'),
            'order' => $request->query->get('order') ?? 'createdAt',
            'direction' => $request->query->get('direction') ?? 'DESC'
        ]);
        $filters->handleRequest($request);

        $requestFilters = [
            'search' => $filters->get('search')->getData(),
            'cities' => $filters->get('cities')->getData(),
            'types' => $filters->get('types')->getData(),
            'state' => $filters->get('state')->getData(),
            'dateStart' => $filters->get('dateStart')->getData(),
            'dateEnd' => $filters->get('dateEnd')->getData(),
            'order' => $filters->get('order')->getData() ?? 'createdAt',
            'direction' => $filters->get('direction')->getData() ?? 'DESC'
        ];
        $items = $this->eventRepository->findByFilters($requestFilters);

        // Get the available events
        $available = $filters->get('available')->getData();
        if ($available && count($available) > 0) {
            if (count($available) === 1 && $available[0] === true)
            {
                $items = array_filter($items, function ($item) {
                    return $item->isMaxParticipantsReached() === false && $item->isPassed() === false;
                });
            }
            else if (count($available) === 1 && $available[0] === false) {
                $items = array_filter($items, function ($item) {
                    return $item->isMaxParticipantsReached() === true;
                });
            }
        }

        $events = $this->paginator->paginate(
            $items,
            $page,
            $limit
        );

        return $this->render('frontend/event/index.html.twig', [
            'events' => $events,
            'filters' => $filters->createView()
        ]);
    }

    #[Route('/create', name: RouteConstants::ROUTE_EVENTS_CREATE, methods: ['GET', 'POST'])]
    public function create(Request $request): Response|RedirectResponse
    {
        $event = new Event();

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOrganizer($this->getUser());
            $this->addUserToEvent($event, $this->getUser());

            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le event a bien été créée');

            return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS);
        }

        return $this->render('frontend/event/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_EVENTS_SHOW, methods: ['GET'])]
    public function show(?Event $event): Response
    {
        $this->checkEvent($event);
        return $this->render('frontend/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: RouteConstants::ROUTE_EVENTS_EDIT, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Event $event): Response|RedirectResponse
    {
        $this->checkEvent($event);

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le event a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS);
            }
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le event n\'a pas pu être modifiée');
        }

        return $this->render('frontend/event/edit.html.twig', [
            'form' => $form,
            'event' => $event
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_EVENTS_DELETE, methods: ['POST'])]
    public function delete(Request $request, ?Event $event): Response|RedirectResponse
    {
        $this->checkEvent($event);

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $this->em->remove($event);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le event a bien été supprimée');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le event n\'a pas pu être supprimée');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS);
    }

    #[Route('/{id}/add', name: RouteConstants::ROUTE_EVENTS_ADD, methods: ['GET', 'POST'])]
    public function add(Request $request, ?Event $event): Response|RedirectResponse
    {
        $this->checkEvent($event);

        $form = $this->createForm(EventParticipationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($event->isMaxParticipantsReached()) {
                    $this->addFlash(ToastConstants::TOAST_ERROR, 'Le nombre maximum de participants est atteint');
                    return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
                }

                $this->addParticipantToEvent($event, $form->get('fullname')->getData());
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le participant a bien été ajouté');

                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
            }
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le participant n\'a pas pu être ajouté');
        }

        return $this->render('frontend/event_participation/create.html.twig', [
            'form' => $form,
            'event' => $event
        ]);
    }

    #[Route('/{id}/join', name: RouteConstants::ROUTE_EVENTS_JOIN, methods: ['POST'])]
    public function join(Request $request, ?Event $event): Response|RedirectResponse
    {
        $this->checkEvent($event);

        if ($this->isCsrfTokenValid('join' . $event->getId(), $request->request->get('_token'))) {
            if ($this->isUserInEvent($event, $this->getUser())) {
                $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous avez déjà rejoint l\'évènement');
                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
            }
            if ($event->isMaxParticipantsReached()) {
                $this->addFlash(ToastConstants::TOAST_ERROR, 'Le nombre maximum de participants est atteint');
                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
            }
            $this->addUserToEvent($event, $this->getUser());
            $this->em->flush();

            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Vous avez bien rejoint l\'évènement');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous n\'avez pas pu rejoindre l\'évènement');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
    }

    #[Route('/{id}/leave', name: RouteConstants::ROUTE_EVENTS_LEAVE, methods: ['POST'])]
    public function leave(Request $request, ?Event $event): Response|RedirectResponse
    {
        $this->checkEvent($event);

        if ($this->isCsrfTokenValid('leave' . $event->getId(), $request->request->get('_token'))) {
            if (!$this->isUserInEvent($event, $this->getUser())) {
                $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous n\'avez pas rejoint l\'évènement');
                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
            }
            $eventParticipation = $this->eventParticipationRepository->findOneBy(['event' => $event, 'user' => $this->getUser()]);
            $event->removeEventParticipation($eventParticipation);
            $this->em->remove($eventParticipation);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Vous avez bien quitté l\'évènement');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous n\'avez pas pu quitter l\'évènement');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $event->getId()]);
    }
}
