<?php

namespace App\Controller\Frontend;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\Event;
use App\Form\EventFormType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em
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

    #[Route('', name: RouteConstants::ROUTE_EVENTS, methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('frontend/event/index.html.twig', [
            'events' => $this->eventRepository->findAll(),
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
            'event' => $event
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
}
