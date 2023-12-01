<?php

namespace App\Controller\Backend;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\Type;
use App\Form\TypeFormType;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/types')]
class TypeController extends AbstractController
{
    public function __construct
    (
        private readonly TypeRepository $typeRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private function checkType(?Type $type): void
    {
        if (!$type instanceof Type) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le type n\'existe pas');
            throw $this->createNotFoundException('Le type n\'existe pas');
        }
    }

    #[Route('', name: RouteConstants::ROUTE_TYPES, methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('backend/type/index.html.twig', [
            'types' => $this->typeRepository->findAll(),
        ]);
    }

    #[Route('/create', name: RouteConstants::ROUTE_TYPES_CREATE, methods: ['GET', 'POST'])]
    public function create(Request $request): Response|RedirectResponse
    {
        $type = new Type();

        $form = $this->createForm(TypeFormType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($type);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le type a bien été créée');

            return $this->redirectToRoute(RouteConstants::ROUTE_TYPES);
        }

        return $this->render('backend/type/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_TYPES_SHOW, methods: ['GET'])]
    public function show(?Type $type): Response
    {
        $this->checkType($type);
        return $this->render('backend/type/show.html.twig', [
            'type' => $type
        ]);
    }

    #[Route('/{id}/edit', name: RouteConstants::ROUTE_TYPES_EDIT, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Type $type): Response|RedirectResponse
    {
        $this->checkType($type);

        $form = $this->createForm(TypeFormType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le type a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_TYPES);
            }
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le type n\'a pas pu être modifiée');
        }

        return $this->render('backend/type/edit.html.twig', [
            'form' => $form,
            'type' => $type
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_TYPES_DELETE, methods: ['POST'])]
    public function delete(Request $request, ?Type $type): Response|RedirectResponse
    {
        $this->checkType($type);

        if ($this->isCsrfTokenValid('delete' . $type->getId(), $request->request->get('_token'))) {
            $this->em->remove($type);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le type a bien été supprimée');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le type n\'a pas pu être supprimée');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_TYPES);
    }
}
