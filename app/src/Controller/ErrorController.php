<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServerErrorHttpException;
use Psr\Log\LoggerInterface;

class ErrorController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gestion des erreurs 404 (Page non trouvée)
     */
    public function show404(Request $request, ?\Throwable $exception = null): Response
    {
        $this->logger->warning('Erreur 404 - Page non trouvée', [
            'url' => $request->getUri(),
            'user_agent' => $request->headers->get('User-Agent'),
            'ip' => $request->getClientIp()
        ]);

        return new Response('
            <html>
                <head><title>Erreur 404</title></head>
                <body>
                    <h1>Page non trouvée</h1>
                    <p>La page que vous recherchez n\'existe pas.</p>
                    <p><a href="/">Retour à l\'accueil</a></p>
                </body>
            </html>
        ', 404);
    }

    /**
     * Gestion des erreurs 403 (Accès refusé)
     */
    public function show403(Request $request, ?\Throwable $exception = null): Response
    {
        $this->logger->warning('Erreur 403 - Accès refusé', [
            'url' => $request->getUri(),
            'user_agent' => $request->headers->get('User-Agent'),
            'ip' => $request->getClientIp()
        ]);

        return new Response('
            <html>
                <head><title>Erreur 403</title></head>
                <body>
                    <h1>Accès refusé</h1>
                    <p>Vous n\'avez pas les permissions nécessaires.</p>
                    <p><a href="/">Retour à l\'accueil</a></p>
                </body>
            </html>
        ', 403);
    }

    /**
     * Gestion des erreurs 500 (Erreur serveur)
     */
    public function show500(Request $request, ?\Throwable $exception = null): Response
    {
        $this->logger->error('Erreur 500 - Erreur serveur', [
            'url' => $request->getUri(),
            'user_agent' => $request->headers->get('User-Agent'),
            'ip' => $request->getClientIp(),
            'exception' => $exception ? $exception->getMessage() : 'Aucune exception'
        ]);

        return new Response('
            <html>
                <head><title>Erreur 500</title></head>
                <body>
                    <h1>Erreur serveur</h1>
                    <p>Une erreur inattendue s\'est produite.</p>
                    <p><a href="/">Retour à l\'accueil</a></p>
                </body>
            </html>
        ', 500);
    }

    /**
     * Gestion générale des erreurs
     */
    public function show(Request $request, ?\Throwable $exception = null): Response
    {
        $statusCode = 500;
        $message = 'Une erreur inattendue s\'est produite.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        // Messages personnalisés selon le type d'erreur
        switch ($statusCode) {
            case 404:
                $message = 'La page que vous recherchez n\'existe pas ou a été déplacée.';
                break;
            case 403:
                $message = 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.';
                break;
            case 400:
                $message = 'La requête est invalide. Veuillez vérifier les informations saisies.';
                break;
            case 401:
                $message = 'Vous devez être connecté pour accéder à cette page.';
                break;
            case 500:
            default:
                $message = 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.';
                break;
        }

        return new Response('
            <html>
                <head><title>Erreur ' . $statusCode . '</title></head>
                <body>
                    <h1>Erreur ' . $statusCode . '</h1>
                    <p>' . $message . '</p>
                    <p><a href="/">Retour à l\'accueil</a></p>
                </body>
            </html>
        ', $statusCode);
    }
} 