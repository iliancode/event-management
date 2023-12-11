<?php

namespace App\Controller\Frontend;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\User;
use App\Form\ProfileEmailFormType;
use App\Form\ProfileFormType;
use App\Form\ProfilePasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profiles')]
class ProfileController extends AbstractController
{
    public function __construct
    (
        private readonly UserRepository $profileRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private function checkUser(?User $profile): void
    {
        if (!$profile instanceof User) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le profile n\'existe pas');
            throw $this->createNotFoundException('Le profile n\'existe pas');
        }
    }

    #[Route('', name: RouteConstants::ROUTE_PROFILES, methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('frontend/profile/index.html.twig', [
            'profiles' => $this->profileRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_PROFILES_SHOW, methods: ['GET'])]
    public function show(?User $profile): Response
    {
        $this->checkUser($profile);
        return $this->render('frontend/profile/show.html.twig', [
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit', name: RouteConstants::ROUTE_PROFILES_EDIT, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);

        $form = $this->createForm(ProfileFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le profile a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES);
            }
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le profile n\'a pas pu être modifiée');
        }

        return $this->render('frontend/profile/edit.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit-email', name: RouteConstants::ROUTE_PROFILES_EDIT_EMAIL, methods: ['GET', 'POST'])]
    public function editEmail(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);

        $form = $this->createForm(ProfileEmailFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if old password is valid
                if (!$profile->isPasswordValid($form->get('oldPassword')->getData())) {
                    $this->addFlash(ToastConstants::TOAST_ERROR, 'L\'ancien mot de passe est incorrect');
                    return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES_EDIT_EMAIL, ['id' => $profile->getId()]);
                }
                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'L\'email a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES);
            }
        }

        return $this->render('frontend/profile/edit_email.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit-password', name: RouteConstants::ROUTE_PROFILES_EDIT_PASSWORD, methods: ['GET', 'POST'])]
    public function editPassword(Request $request, ?User $profile, UserPasswordHasherInterface $userPasswordHasher): Response|RedirectResponse
    {
        $this->checkUser($profile);

        $form = $this->createForm(ProfilePasswordFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if old password is valid
                if (!$profile->isPasswordValid($form->get('oldPassword')->getData())) {
                    $this->addFlash(ToastConstants::TOAST_ERROR, 'L\'ancien mot de passe est incorrect');
                    return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES_EDIT_PASSWORD, ['id' => $profile->getId()]);
                }
                // encode the plain password
                $profile->setPassword(
                    $userPasswordHasher->hashPassword(
                        $profile,
                        $form->get('plainPassword')->getData()
                    )
                );

                $this->em->flush();
                $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le mot de passe a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES);
            }
        }

        return $this->render('frontend/profile/edit_password.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}', name: RouteConstants::ROUTE_PROFILES_DELETE, methods: ['POST'])]
    public function delete(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);

        if ($this->isCsrfTokenValid('delete' . $profile->getId(), $request->request->get('_token'))) {
            $this->em->remove($profile);
            $this->em->flush();
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Le profile a bien été supprimée');
        } else {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Le profile n\'a pas pu être supprimée');
        }

        return $this->redirectToRoute(RouteConstants::ROUTE_PROFILES);
    }
}
