<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant l'authentification et la sécurité
 * 
 * Ce contrôleur gère :
 * - La connexion des utilisateurs
 * - La déconnexion des utilisateurs
 * - La gestion des erreurs d'authentification
 * - La redirection des utilisateurs déjà connectés
 */
class SecurityController extends AbstractController
{
    /**
     * Page de connexion
     * 
     * Cette méthode gère :
     * - La vérification si l'utilisateur est déjà connecté
     * - L'affichage du formulaire de connexion
     * - La gestion des erreurs d'authentification
     * - La redirection vers la page d'accueil si déjà connecté
     * - Le logging pour le diagnostic
     * 
     * @param AuthenticationUtils $authenticationUtils Service d'authentification Symfony
     * @return Response Page de connexion ou redirection
     */
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Log pour diagnostiquer l'état de connexion
        error_log('SecurityController::login - User: ' . ($this->getUser() ? 'connected' : 'not connected'));
        
        // Vérification si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            error_log('User already connected, redirecting to home page');
            return $this->redirectToRoute('app_home');
        }

        // Récupération des informations d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Gestion des erreurs d'authentification
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe incorrect. Veuillez réessayer.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Déconnexion des utilisateurs
     * 
     * Cette méthode est vide car la déconnexion est gérée automatiquement
     * par le firewall de sécurité Symfony. La route est définie pour
     * permettre la configuration du firewall.
     * 
     * @return void
     */
    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Le contrôleur peut rester vide, la déconnexion est gérée par le firewall
    }
} 