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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PaymentController extends AbstractController
{
    private StripeClient $stripeClient;
    private string $stripePublicKey;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private StripeWebhookService $webhookService;
    private EmailService $emailService;
    private VehiculeStatusService $vehiculeStatusService;

    public function __construct(
        StripeClient $stripeClient, 
        string $stripePublicKey,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        StripeWebhookService $webhookService,
        EmailService $emailService,
        VehiculeStatusService $vehiculeStatusService
    ) {
        $this->stripeClient = $stripeClient;
        $this->stripePublicKey = $stripePublicKey;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->webhookService = $webhookService;
        $this->emailService = $emailService;
        $this->vehiculeStatusService = $vehiculeStatusService;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        // Récupérer les commandes en attente de paiement de l'utilisateur
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer un paiement.');
            return $this->redirectToRoute('app_login');
        }

        $pendingOrders = $this->entityManager->getRepository(Orders::class)
            ->findBy(['users' => $user, 'orderStatus' => 'pending']);

        return $this->render('payment/index.html.twig', [
            'stripe_public_key' => $this->stripePublicKey,
            'pending_orders' => $pendingOrders,
        ]);
    }

    #[Route('/pay', name: 'app_pay')]
    public function pay(): Response
    {
        return $this->redirectToRoute('app_payment');
    }

    #[Route('/payment/order/{id}', name: 'app_payment_order')]
    public function payOrder(Orders $order): Response
    {
        $user = $this->getUser();
        if (!$user || $order->getUsers() !== $user) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_payment');
        }

        if ($order->getOrderStatus() !== 'pending') {
            $this->addFlash('error', 'Cette commande ne peut plus être payée.');
            return $this->redirectToRoute('app_payment');
        }

        return $this->render('payment/order.html.twig', [
            'stripe_public_key' => $this->stripePublicKey,
            'order' => $order,
        ]);
    }

    #[Route('/create-checkout-session', name: 'app_create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): Response
    {
        try {
            $orderId = $request->request->get('order_id');
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);

            if (!$order) {
                throw new \Exception('Commande non trouvée.');
            }

            $user = $this->getUser();
            if (!$user || $order->getUsers() !== $user) {
                throw new \Exception('Accès non autorisé.');
            }

            if ($order->getOrderStatus() !== 'pending') {
                throw new \Exception('Cette commande ne peut plus être payée.');
            }

            // Récupérer la photo principale du véhicule
            $vehicule = $order->getVehicules()->first();
            $vehiculeImage = null;
            if ($vehicule && $vehicule->getPictures()->count() > 0) {
                $vehiculeImage = $request->getSchemeAndHttpHost() . '/uploads/vehicules/' . $vehicule->getPictures()->first()->getName();
            }

            // Créer la session de paiement Stripe
            $checkoutSession = $this->stripeClient->checkout->sessions->create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Commande #' . $order->getId(),
                            'description' => 'Paiement de commande',
                            'images' => $vehiculeImage ? [$vehiculeImage] : [],
                        ],
                        'unit_amount' => (int)($order->getTotalPrice() * 100), // Convertir en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_payment_success', [
                    'session_id' => '{CHECKOUT_SESSION_ID}',
                    'order_id' => $order->getId()
                ], Response::HTTP_ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_payment_cancel', [
                    'order_id' => $order->getId()
                ], Response::HTTP_ABSOLUTE_URL),
                'metadata' => [
                    'order_id' => $order->getId(),
                    'user_id' => $user->getId(),
                ],
            ]);

            // Mettre à jour la commande avec l'ID de session Stripe
            $this->entityManager->flush();

            return new JsonResponse(['url' => $checkoutSession->url]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session de paiement', [
                'error' => $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);

            return new JsonResponse([
                'error' => 'Erreur lors de la création de la session de paiement: ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        $orderId = $request->query->get('order_id');

        try {
            if ($sessionId) {
                // Récupérer les détails de la session Stripe
                $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
                
                if ($session->payment_status === 'paid') {
                    // Mettre à jour la commande
                    $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
                    if ($order) {
                        $order->setOrderStatus('paid');
                        
                        // Marquer le véhicule comme vendu
                        $vehicule = $order->getVehicules()->first();
                        if ($vehicule) {
                            $this->vehiculeStatusService->markVehiculeAsSold($order);
                            $this->logger->info('Véhicule marqué comme vendu après paiement réussi', [
                                'vehicule_id' => $vehicule->getId(),
                                'order_id' => $order->getId(),
                                'session_id' => $sessionId,
                            ]);
                        }
                        
                        // Créer un enregistrement de paiement
                        $payment = new Payement();
                        $payment->setOrders($order);
                        $payment->setAmount($order->getTotalPrice());
                        $payment->setPayementStatus('completed');
                        $payment->setPayementAt(new \DateTimeImmutable());
                        
                        $this->entityManager->persist($payment);
                        $this->entityManager->flush();
                        
                        // Envoyer les emails de confirmation
                        try {
                            $user = $order->getUsers();
                            $this->emailService->sendPurchaseConfirmation($order, $user);
                            $this->emailService->sendAdminNotification($order, $user);
                        } catch (\Exception $emailException) {
                            $this->logger->error('Erreur lors de l\'envoi des emails de confirmation', [
                                'error' => $emailException->getMessage(),
                                'order_id' => $order->getId(),
                            ]);
                        }
                        
                        $this->addFlash('success', 'Paiement effectué avec succès ! Votre commande a été confirmée et un email de confirmation vous a été envoyé.');
                    }
                }
            }

            return $this->render('payment/success.html.twig', [
                'order_id' => $orderId,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du succès de paiement', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'order_id' => $orderId,
            ]);

            $this->addFlash('error', 'Erreur lors du traitement du paiement. Contactez le support.');
            return $this->redirectToRoute('app_payment');
        }
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function paymentCancel(Request $request): Response
    {
        $orderId = $request->query->get('order_id');
        
        $this->addFlash('warning', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
        
        return $this->render('payment/cancel.html.twig', [
            'order_id' => $orderId,
        ]);
    }

    #[Route('/webhook/stripe', name: 'app_webhook_stripe', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        
        // Utiliser le paramètre configuré au lieu de $_ENV
        $endpointSecret = $this->getParameter('stripe_webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error('Webhook Stripe - Payload invalide', ['error' => $e->getMessage()]);
            return new Response('Payload invalide', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logger->error('Webhook Stripe - Signature invalide', ['error' => $e->getMessage()]);
            return new Response('Signature invalide', 400);
        }

        $this->logger->info('Webhook Stripe reçu', [
            'event_type' => $event->type,
            'event_id' => $event->id,
        ]);

        try {
            // Utiliser le service webhook pour traiter l'événement
            $this->webhookService->processWebhookEvent($event->type, $event->data->object);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du webhook Stripe', [
                'error' => $e->getMessage(),
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);
            return new Response('Erreur interne', 500);
        }

        return new Response('Webhook traité avec succès', 200);
    }

    #[Route('/webhook/stripe/status', name: 'app_webhook_status', methods: ['GET'])]
    public function webhookStatus(): Response
    {
        return $this->render('payment/webhook_status.html.twig', [
            'supported_events' => $this->webhookService->getSupportedEvents(),
            'webhook_url' => $this->generateUrl('app_webhook_stripe', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
