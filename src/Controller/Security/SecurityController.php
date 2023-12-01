<?php

namespace App\Controller\Security;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Constants\UserConstants;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Utils\SecurityUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct
    (
        private readonly SecurityUtils $securityUtils,
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * Method to login
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route(path: '/login', name: RouteConstants::ROUTE_LOGIN, methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already authenticated
        if ($this->securityUtils->isUserAuthenticated()) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous êtes déjà connecté !');
            return $this->redirectToRoute(RouteConstants::ROUTE_HOME);
        }

        // Check if there is any error
        $error = $authenticationUtils->getLastAuthenticationError();

        // Retrieve the last username
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Method to register
     *
     * @param Request $request
     * @return Response
     */
    #[Route(path: '/register', name: RouteConstants::ROUTE_REGISTER, methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        // Check if the user is already authenticated
        if ($this->securityUtils->isUserAuthenticated()) {
            $this->addFlash(ToastConstants::TOAST_ERROR, 'Vous êtes déjà connecté !');
            return $this->redirectToRoute(RouteConstants::ROUTE_HOME);
        }

        // Create a new user
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the password
            $user->setPassword($this->securityUtils->encodePassword($user, $user->getPassword()));

            // Set the role to ROLE_USER (not necessary because it's the default value but it's better to be explicit)
            $user->setRoles([UserConstants::ROLE_USER]);

            // Persist the user
            $this->em->persist($user);
            $this->em->flush();

            // Add a flash message
            $this->addFlash(ToastConstants::TOAST_SUCCESS, 'Votre compte a bien été créé !');

            // Redirect the user
            return $this->redirectToRoute(RouteConstants::ROUTE_HOME);
        }

        return $this->render('security/register.html.twig', [
            'form' => $form
        ]);
    }


    /**
     * Method to logout
     *
     * @return void
     */
    #[Route(path: '/logout', name: RouteConstants::ROUTE_LOGOUT)]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
