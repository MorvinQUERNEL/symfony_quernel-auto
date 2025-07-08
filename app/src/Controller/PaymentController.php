<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Entity\Orders;
use App\Entity\Payement;
use App\Service\StripeWebhookService;
use App\Service\EmailService;
use App\Service\VehiculeStatusService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Contrôleur gérant les paiements et l'intégration Stripe
 * 
 * Ce contrôleur gère :
 * - L'affichage des commandes en attente de paiement
 * - La création de sessions de paiement Stripe
 * - Le traitement des paiements réussis et annulés
 * - Les webhooks Stripe pour les événements de paiement
 * - L'envoi d'emails de confirmation
 * - La mise à jour des statuts de commande et véhicule
 */
class PaymentController extends AbstractController
{
    private StripeClient $stripeClient;
    private string $stripePublicKey;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private StripeWebhookService $webhookService;
    private EmailService $emailService;
    private VehiculeStatusService $vehiculeStatusService;
    private StripeService $stripeService;

    /**
     * Constructeur avec injection des dépendances
     * 
     * @param StripeClient $stripeClient Client Stripe pour les paiements
     * @param string $stripePublicKey Clé publique Stripe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param LoggerInterface $logger Service de logging
     * @param StripeWebhookService $webhookService Service de traitement des webhooks
     * @param EmailService $emailService Service d'envoi d'emails
     * @param VehiculeStatusService $vehiculeStatusService Service de gestion des statuts véhicule
     * @param StripeService $stripeService Service principal Stripe
     */
    public function __construct(
        StripeClient $stripeClient, 
        string $stripePublicKey,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        StripeWebhookService $webhookService,
        EmailService $emailService,
        VehiculeStatusService $vehiculeStatusService,
        StripeService $stripeService
    ) {
        $this->stripeClient = $stripeClient;
        $this->stripePublicKey = $stripePublicKey;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->webhookService = $webhookService;
        $this->emailService = $emailService;
        $this->vehiculeStatusService = $vehiculeStatusService;
        $this->stripeService = $stripeService;
    }

    /**
     * Page principale des paiements
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification de l'utilisateur
     * - L'affichage des commandes en attente de paiement
     * - Le rendu de la page de paiement avec la clé publique Stripe
     * 
     * @return Response Page de paiement ou redirection vers la connexion
     */
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        // Vérification de l'authentification
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer un paiement.');
            return $this->redirectToRoute('app_login');
        }

        // Récupération des commandes en attente de paiement de l'utilisateur
        $pendingOrders = $this->entityManager->getRepository(Orders::class)
            ->findBy(['users' => $user, 'orderStatus' => 'pending']);

        return $this->render('payment/index.html.twig', [
            'stripe_public_key' => $this->stripePublicKey,
            'pending_orders' => $pendingOrders,
        ]);
    }

    /**
     * Redirection vers la page de paiement
     * 
     * @return Response Redirection vers la page de paiement
     */
    #[Route('/pay', name: 'app_pay')]
    public function pay(): Response
    {
        return $this->redirectToRoute('app_payment');
    }

    /**
     * Paiement d'une commande spécifique
     * 
     * Cette méthode gère :
     * - La vérification des autorisations (propriétaire de la commande)
     * - La vérification du statut de la commande
     * - L'affichage de la page de paiement pour une commande spécifique
     * 
     * @param Orders $order Commande à payer
     * @return Response Page de paiement de la commande ou redirection
     */
    #[Route('/payment/order/{id}', name: 'app_payment_order')]
    public function payOrder(Orders $order): Response
    {
        $user = $this->getUser();
        
        // Vérification des autorisations
        if (!$user || $order->getUsers() !== $user) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_payment');
        }

        // Vérification du statut de la commande
        if ($order->getOrderStatus() !== 'pending') {
            $this->addFlash('error', 'Cette commande ne peut plus être payée.');
            return $this->redirectToRoute('app_payment');
        }

        return $this->render('payment/order.html.twig', [
            'stripe_public_key' => $this->stripePublicKey,
            'order' => $order,
        ]);
    }

    /**
     * Création d'une session de paiement Stripe Checkout
     * 
     * Cette méthode gère :
     * - La validation de la commande et des autorisations
     * - La création d'une session de paiement via le service Stripe
     * - Le retour des données de session au frontend
     * - La gestion des erreurs avec logging
     * 
     * @param Request $request Requête HTTP contenant l'ID de commande
     * @return JsonResponse Données de session Stripe ou erreur
     */
    #[Route('/create-checkout-session', name: 'app_create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): Response
    {
        try {
            $orderId = $request->request->get('order_id');
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);

            // Validation de la commande
            if (!$order) {
                throw new \Exception('Commande non trouvée.');
            }

            $user = $this->getUser();
            
            // Validation des autorisations
            if (!$user || $order->getUsers() !== $user) {
                throw new \Exception('Accès non autorisé.');
            }

            // Validation du statut de la commande
            if ($order->getOrderStatus() !== 'pending') {
                throw new \Exception('Cette commande ne peut plus être payée.');
            }

            // Création de la session de paiement via le service
            $checkoutData = $this->stripeService->createCheckoutSession($order, $request->getSchemeAndHttpHost());

            return new JsonResponse($checkoutData);

        } catch (\Exception $e) {
            // Logging de l'erreur pour le debugging
            $this->logger->error('Erreur lors de la création de la session de paiement', [
                'error' => $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);

            return new JsonResponse([
                'error' => 'Erreur lors de la création de la session de paiement: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Création d'un PaymentIntent Stripe
     * 
     * Cette méthode gère :
     * - La validation de la commande et des autorisations
     * - La création d'un PaymentIntent via le service Stripe
     * - Le retour des données de paiement au frontend
     * - La gestion des erreurs avec logging
     * 
     * @param Request $request Requête HTTP contenant l'ID de commande
     * @return JsonResponse Données de PaymentIntent ou erreur
     */
    #[Route('/create-payment-intent', name: 'app_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): Response
    {
        try {
            $orderId = $request->request->get('order_id');
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);

            // Validation de la commande
            if (!$order) {
                throw new \Exception('Commande non trouvée.');
            }

            $user = $this->getUser();
            
            // Validation des autorisations
            if (!$user || $order->getUsers() !== $user) {
                throw new \Exception('Accès non autorisé.');
            }

            // Validation du statut de la commande
            if ($order->getOrderStatus() !== 'pending') {
                throw new \Exception('Cette commande ne peut plus être payée.');
            }

            // Création du PaymentIntent via le service
            $paymentIntentData = $this->stripeService->createPaymentIntent($order);

            return new JsonResponse($paymentIntentData);

        } catch (\Exception $e) {
            // Logging de l'erreur pour le debugging
            $this->logger->error('Erreur lors de la création du PaymentIntent', [
                'error' => $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);

            return new JsonResponse([
                'error' => 'Erreur lors de la création du PaymentIntent: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Traitement du succès d'un paiement
     * 
     * Cette méthode gère :
     * - La récupération des informations de session Stripe
     * - La validation de la commande et des autorisations
     * - La mise à jour du statut de la commande et du véhicule
     * - L'envoi d'email de confirmation
     * - L'affichage de la page de succès
     * 
     * @param Request $request Requête HTTP contenant l'ID de session Stripe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param StripeService $stripeService Service Stripe
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @return Response Page de succès ou redirection
     */
    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(
        Request $request, 
        EntityManagerInterface $entityManager, 
        StripeService $stripeService,
        MailerInterface $mailer
    ): Response
    {
        $stripeSessionId = $request->query->get('session_id');
        
        // Vérification de la présence de l'ID de session
        if (!$stripeSessionId) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupération des détails de la session Stripe
            $session = $stripeService->getStripeClient()->checkout->sessions->retrieve($stripeSessionId);
            $orderId = $session->metadata->order_id ?? null;

            // Validation de l'ID de commande
            if (!$orderId) {
                $this->addFlash('error', 'ID de commande manquant dans la session Stripe.');
                return $this->redirectToRoute('app_profile');
            }

            // Récupération et validation de la commande
            $order = $entityManager->getRepository(Orders::class)->find($orderId);
            $user = $order->getUsers();

            if (!$order || $this->getUser() !== $user) {
                $this->addFlash('error', 'Commande non trouvée ou accès non autorisé.');
                return $this->redirectToRoute('app_profile');
            }

            // Mise à jour du statut si le paiement n'est pas déjà traité
            if ($order->getOrderStatus() !== 'paid') {
                $order->setOrderStatus('paid');
                $order->getVehicule()->setStatus('Vendu');
                $entityManager->flush();

                // Envoi de l'email de confirmation de commande
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@quernel-auto.fr', 'Quernel Auto'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre commande #' . $order->getId())
                    ->htmlTemplate('emails/purchase_confirmation.html.twig')
                    ->context([
                        'user' => $user,
                        'order' => $order
                    ]);

                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    // Ne pas bloquer l'utilisateur, mais logger l'erreur
                    // Le logger est déjà injecté dans d'autres contrôleurs, il faudrait l'ajouter ici si nécessaire
                }
            }

            $this->addFlash('success', 'Paiement effectué avec succès ! Votre commande a été confirmée et un email de confirmation vous a été envoyé.');
            return $this->render('payment/success.html.twig', [
                'order' => $order
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du traitement du paiement. Contactez le support.');
            return $this->redirectToRoute('app_profile');
        }
    }

    /**
     * Traitement de l'annulation d'un paiement
     * 
     * Cette méthode gère :
     * - L'affichage d'un message d'annulation
     * - Le rendu de la page d'annulation avec l'ID de commande
     * 
     * @param Request $request Requête HTTP contenant l'ID de commande
     * @return Response Page d'annulation de paiement
     */
    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function paymentCancel(Request $request): Response
    {
        $orderId = $request->query->get('order_id');
        
        $this->addFlash('warning', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
        
        return $this->render('payment/cancel.html.twig', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Webhook Stripe pour les événements de paiement
     * 
     * Cette méthode gère :
     * - La validation de la signature du webhook Stripe
     * - Le traitement des événements de paiement (succès, échec, etc.)
     * - La mise à jour automatique des statuts de commande
     * - L'envoi d'emails de notification
     * - La gestion des erreurs avec logging
     * 
     * @param Request $request Requête HTTP contenant le payload du webhook
     * @return Response Statut de traitement du webhook
     */
    #[Route('/webhook/stripe', name: 'app_webhook_stripe', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        
        // Récupération du secret webhook depuis la configuration
        $endpointSecret = $this->getParameter('stripe_webhook_secret');

        try {
            // Construction et validation de l'événement Stripe
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error('Webhook Stripe - Payload invalide', ['error' => $e->getMessage()]);
            return new Response('Payload invalide', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logger->error('Webhook Stripe - Signature invalide', ['error' => $e->getMessage()]);
            return new Response('Signature invalide', 400);
        }

        // Logging de la réception du webhook
        $this->logger->info('Webhook Stripe reçu', [
            'event_type' => $event->type,
            'event_id' => $event->id,
        ]);

        try {
            // Traitement de l'événement via le service webhook
            $this->webhookService->processWebhookEvent($event->type, $event->data->object);
        } catch (\Exception $e) {
            // Logging de l'erreur de traitement
            $this->logger->error('Erreur lors du traitement du webhook Stripe', [
                'error' => $e->getMessage(),
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);
            return new Response('Erreur interne', 500);
        }

        return new Response('Webhook traité avec succès', 200);
    }

    /**
     * Page de statut des webhooks Stripe
     * 
     * Cette méthode gère :
     * - L'affichage des événements webhook supportés
     * - L'affichage de l'URL du webhook pour configuration
     * - Le rendu de la page de statut
     * 
     * @return Response Page de statut des webhooks
     */
    #[Route('/webhook/stripe/status', name: 'app_webhook_status', methods: ['GET'])]
    public function webhookStatus(): Response
    {
        return $this->render('payment/webhook_status.html.twig', [
            'supported_events' => $this->webhookService->getSupportedEvents(),
            'webhook_url' => $this->generateUrl('app_webhook_stripe', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
