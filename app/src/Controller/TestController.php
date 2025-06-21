<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function test(): Response
    {
        return new Response('
            <html>
                <head><title>Test Page</title></head>
                <body>
                    <h1>Page de test</h1>
                    <p>Si vous voyez cette page, le problème n\'est pas avec Symfony.</p>
                    <p>Utilisateur connecté : ' . ($this->getUser() ? 'OUI' : 'NON') . '</p>
                    <p><a href="/">Accueil</a></p>
                    <p><a href="/connexion">Connexion</a></p>
                    <p><a href="/vehicules">Véhicules</a></p>
                </body>
            </html>
        ');
    }
} 