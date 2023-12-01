<?php

namespace App\Controller\Auth;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Utils\SecurityUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct
    (
        private readonly SecurityUtils $securityUtils
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
