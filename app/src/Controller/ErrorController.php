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
     * Page de test pour les erreurs (uniquement en mode développement)
     */
    #[Route('/test-errors', name: 'app_test_errors')]
    public function testErrors(): Response
    {
        // Vérifier si on est en mode développement
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException('Cette page n\'est disponible qu\'en mode développement.');
        }

        return $this->render('error/test.html.twig');
    }

    /**
     * Test erreur 404
     */
    #[Route('/test-errors/404', name: 'app_test_error_404')]
    public function testError404(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        throw new NotFoundHttpException('Page de test pour l\'erreur 404');
    }

    /**
     * Test erreur 403
     */
    #[Route('/test-errors/403', name: 'app_test_error_403')]
    public function testError403(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        throw new AccessDeniedHttpException('Page de test pour l\'erreur 403');
    }

    /**
     * Test erreur 500
     */
    #[Route('/test-errors/500', name: 'app_test_error_500')]
    public function testError500(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        throw new HttpException(500, 'Page de test pour l\'erreur 500');
    }

    /**
     * Test erreur générique
     */
    #[Route('/test-errors/generic', name: 'app_test_error_generic')]
    public function testErrorGeneric(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        throw new HttpException(418, 'Je suis une théière - Page de test pour erreur générique');
    }

    /**
     * Gestion générale des erreurs
     */
    public function show(Request $request, ?\Throwable $exception = null): Response
    {
        $statusCode = 500;
        $statusText = 'Internal Server Error';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $statusText = Response::$statusTexts[$statusCode] ?? 'Unknown Error';
        }

        // Log de l'erreur
        $this->logger->error('Erreur ' . $statusCode, [
            'url' => $request->getUri(),
            'user_agent' => $request->headers->get('User-Agent'),
            'ip' => $request->getClientIp(),
            'exception' => $exception ? $exception->getMessage() : 'Aucune exception'
        ]);

        // Sélectionner le template approprié
        $template = match($statusCode) {
            404 => 'bundles/TwigBundle/Exception/error404.html.twig',
            403 => 'bundles/TwigBundle/Exception/error403.html.twig',
            500 => 'bundles/TwigBundle/Exception/error500.html.twig',
            default => 'bundles/TwigBundle/Exception/error.html.twig'
        };

        return $this->render($template, [
            'status_code' => $statusCode,
            'status_text' => $statusText,
            'exception' => $exception
        ], new Response('', $statusCode));
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

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'status_code' => 404,
            'status_text' => 'Not Found',
            'exception' => $exception
        ], new Response('', 404));
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

        return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [
            'status_code' => 403,
            'status_text' => 'Forbidden',
            'exception' => $exception
        ], new Response('', 403));
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

        return $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
            'status_code' => 500,
            'status_text' => 'Internal Server Error',
            'exception' => $exception
        ], new Response('', 500));
    }

    /**
     * Prévisualisation de la page d'erreur 404 avec style
     */
    #[Route('/preview-errors/404', name: 'app_preview_error_404')]
    public function previewError404(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'status_code' => 404,
            'status_text' => 'Not Found',
            'exception' => null
        ]);
    }

    /**
     * Prévisualisation de la page d'erreur 403 avec style
     */
    #[Route('/preview-errors/403', name: 'app_preview_error_403')]
    public function previewError403(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [
            'status_code' => 403,
            'status_text' => 'Forbidden',
            'exception' => null
        ]);
    }

    /**
     * Prévisualisation de la page d'erreur 500 avec style
     */
    #[Route('/preview-errors/500', name: 'app_preview_error_500')]
    public function previewError500(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        return $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
            'status_code' => 500,
            'status_text' => 'Internal Server Error',
            'exception' => null
        ]);
    }

    /**
     * Prévisualisation de la page d'erreur générique avec style
     */
    #[Route('/preview-errors/generic', name: 'app_preview_error_generic')]
    public function previewErrorGenericStyled(): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => 418,
            'status_text' => 'I\'m a teapot',
            'exception' => null
        ]);
    }
} 