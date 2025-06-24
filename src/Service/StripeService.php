<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Payement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeClient $stripeClient;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private VehiculeStatusService $vehiculeStatusService;
    private EmailService $emailService;

    public function __construct(
        StripeClient $stripeClient,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        VehiculeStatusService $vehiculeStatusService,
        EmailService $emailService
    ) {
        $this->stripeClient = $stripeClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->vehiculeStatusService = $vehiculeStatusService;
        $this->emailService = $emailService;
    }

    /**
     * Crée une session de paiement Stripe pour une commande
     */
    public function createCheckoutSession(Orders $order, string $baseUrl): array
    {
        $vehicule = $order->getVehicules()->first();
        $vehiculeImage = null;
        
        if ($vehicule && $vehicule->getPictures()->count() > 0) {
            $vehiculeImage = $baseUrl . '/uploads/vehicules/' . $vehicule->getPictures()->first()->getName();
        }

        $sessionConfig = [
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande #' . $order->getId(),
                        'description' => 'Paiement de commande',
                        'images' => $vehiculeImage ? [$vehiculeImage] : [],
                    ],
                    'unit_amount' => (int)($order->getTotalPrice() * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('app_payment_success', [
                'session_id' => '{CHECKOUT_SESSION_ID}',
                'order_id' => $order->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_payment_cancel', [
                'order_id' => $order->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'order_id' => $order->getId(),
                'user_id' => $order->getUsers()->getId(),
            ],
            'payment_method_types' => ['card', 'apple_pay'],
            'payment_method_options' => [
                'apple_pay' => [
                    'enabled' => true,
                ],
            ],
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['FR', 'BE', 'CH', 'CA'],
            ],
        ];

        $checkoutSession = $this->stripeClient->checkout->sessions->create($sessionConfig);
        
        return ['url' => $checkoutSession->url];
    }

    /**
     * Crée un PaymentIntent pour Apple Pay
     */
    public function createPaymentIntent(Orders $order): array
    {
        $paymentIntent = $this->stripeClient->paymentIntents->create([
            'amount' => (int)($order->getTotalPrice() * 100),
            'currency' => 'eur',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'order_id' => $order->getId(),
                'user_id' => $order->getUsers()->getId(),
            ],
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
        ];
    }

    /**
     * Traite le succès d'un paiement
     */
    public function processPaymentSuccess(string $sessionId, int $orderId): void
    {
        $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
        
        if ($session->payment_status === 'paid') {
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
            }
        }
    }
} 