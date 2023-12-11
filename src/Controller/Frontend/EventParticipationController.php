<?php

namespace App\Controller\Frontend;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\EventParticipation;
use App\Entity\User;
use App\Form\EventParticipationFormType;
use App\Repository\EventParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/eventParticipations')]
class EventParticipationController extends AbstractController
{
    public function __construct
    (
        private readonly EntityManagerInterface $em
    )
    {
    }

    private function checkEventParticipation(?EventParticipation $eventParticipation): void
    {
        if (!$eventParticipation instanceof EventParticipation) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le eventParticipation n\'existe pas');
            throw $this->createNotFoundException('Le eventParticipation n\'existe pas');
        }
    }

    #[Route('/{id}/edit', name: RouteConstants::ROUTE_EVENT_PARTICIPATIONS_EDIT, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?EventParticipation $eventParticipation): Response|RedirectResponse
    {
        $this->checkEventParticipation($eventParticipation);

        $form = $this->createForm(EventParticipationFormType::class, $eventParticipation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le eventParticipation a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $eventParticipation->getEvent()->getId()]);
            }
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le eventParticipation n\'a pas pu être modifiée');
        }

        return $this->render('frontend/event_participation/edit.html.twig', [
            'form' => $form,
            'eventParticipation' => $eventParticipation
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_EVENT_PARTICIPATIONS_DELETE, methods: ['POST'])]
    public function delete(Request $request, ?EventParticipation $eventParticipation): Response|RedirectResponse
    {
        $this->checkEventParticipation($eventParticipation);

        if ($this->isCsrfTokenValid('delete' . $eventParticipation->getId(), $request->request->get('_token'))) {
            $this->em->remove($eventParticipation);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le eventParticipation a bien été supprimée');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le eventParticipation n\'a pas pu être supprimée');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $eventParticipation->getEvent()->getId()]);
    }

    #[Route('/{id}/ban', name: RouteConstants::ROUTE_EVENT_PARTICIPATIONS_BAN, methods: ['GET', 'POST'])]
    public function ban(Request $request, ?EventParticipation $eventParticipation): Response|RedirectResponse
    {
        $this->checkEventParticipation($eventParticipation);
        $banned = $eventParticipation->isBanned();
        $eventParticipation->setBanned(!$banned);
        $this->em->flush();
        $message = $banned ? 'Le eventParticipation a bien été débanni' : 'Le eventParticipation a bien été banni';
        $this->addFlash(ToastConstants::TOAST_SUCCESS, $message);

        return $this->redirectToRoute(RouteConstants::ROUTE_EVENTS_SHOW, ['id' => $eventParticipation->getEvent()->getId()]);
    }
}
