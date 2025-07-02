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

            $checkoutData = $this->stripeService->createCheckoutSession($order, $request->getSchemeAndHttpHost());

            return new JsonResponse($checkoutData);

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

    #[Route('/create-payment-intent', name: 'app_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): Response
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

            $paymentIntentData = $this->stripeService->createPaymentIntent($order);

            return new JsonResponse($paymentIntentData);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du PaymentIntent', [
                'error' => $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);

            return new JsonResponse([
                'error' => 'Erreur lors de la création du PaymentIntent: ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(
        Request $request, 
        EntityManagerInterface $entityManager, 
        StripeService $stripeService,
        MailerInterface $mailer
    ): Response
    {
        $stripeSessionId = $request->query->get('session_id');
        
        if (!$stripeSessionId) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $session = $stripeService->getStripeClient()->checkout->sessions->retrieve($stripeSessionId);
            $orderId = $session->metadata->order_id ?? null;

            if (!$orderId) {
                $this->addFlash('error', 'ID de commande manquant dans la session Stripe.');
                return $this->redirectToRoute('app_profile');
            }

            $order = $entityManager->getRepository(Orders::class)->find($orderId);
            $user = $order->getUsers();

            if (!$order || $this->getUser() !== $user) {
                $this->addFlash('error', 'Commande non trouvée ou accès non autorisé.');
                return $this->redirectToRoute('app_profile');
            }

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
