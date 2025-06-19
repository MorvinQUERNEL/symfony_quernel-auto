<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    private RouterInterface $router;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(
        RouterInterface $router,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Ne pas intercepter les exceptions en mode debug
        if ($_ENV['APP_ENV'] === 'dev') {
            return;
        }

        $statusCode = 500;
        $message = 'Une erreur inattendue s\'est produite.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        // Messages personnalisés selon le type d'erreur
        switch ($statusCode) {
            case 404:
                $message = 'La page que vous recherchez n\'existe pas ou a été déplacée.';
                $this->logger->warning('Erreur 404 interceptée', [
                    'url' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'ip' => $request->getClientIp()
                ]);
                break;
            case 403:
                $message = 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.';
                $this->logger->warning('Erreur 403 interceptée', [
                    'url' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'ip' => $request->getClientIp()
                ]);
                break;
            case 400:
                $message = 'La requête est invalide. Veuillez vérifier les informations saisies.';
                $this->logger->warning('Erreur 400 interceptée', [
                    'url' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'ip' => $request->getClientIp()
                ]);
                break;
            case 401:
                $message = 'Vous devez être connecté pour accéder à cette page.';
                $this->logger->info('Erreur 401 interceptée', [
                    'url' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'ip' => $request->getClientIp()
                ]);
                break;
            case 500:
            default:
                $message = 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.';
                $this->logger->error('Erreur serveur interceptée', [
                    'url' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'ip' => $request->getClientIp(),
                    'exception' => $exception->getMessage(),
                    'status_code' => $statusCode
                ]);
                break;
        }

        // Ajouter le message flash via la session de la requête
        if ($request->hasSession()) {
            $request->getSession()->getFlashBag()->add('error', $message);
        }

        // Rediriger vers la page d'accueil
        $response = new RedirectResponse($this->router->generate('app_home'));
        $event->setResponse($response);
    }
} 