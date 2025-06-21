<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Log pour diagnostiquer
        error_log('SecurityController::login - User: ' . ($this->getUser() ? 'connected' : 'not connected'));
        
        // Si l'utilisateur est déjà connecté, le rediriger vers la page de test
        if ($this->getUser()) {
            error_log('User already connected, redirecting to test page');
            return $this->redirectToRoute('app_test');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe incorrect. Veuillez réessayer.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Le contrôleur peut rester vide, la déconnexion est gérée par le firewall
    }
} 