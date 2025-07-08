<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Payement;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Service de gestion des paiements Stripe
 * 
 * Ce service gère :
 * - La création de sessions de paiement Stripe Checkout
 * - La création de PaymentIntents pour Apple Pay
 * - Le traitement des paiements réussis
 * - La mise à jour des statuts de commande et véhicule
 * - L'enregistrement des paiements en base de données
 * - L'envoi d'emails de confirmation
 * - La gestion des erreurs et logging
 * 
 * Intègre Stripe pour les paiements sécurisés avec support
 * des cartes bancaires et Apple Pay.
 */
class StripeService
{
    private StripeClient $stripeClient;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private VehiculeStatusService $vehiculeStatusService;
    private EmailService $emailService;

    /**
     * Constructeur du service Stripe
     * 
     * @param StripeClient $stripeClient Client Stripe configuré
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param LoggerInterface $logger Service de logging
     * @param UrlGeneratorInterface $urlGenerator Générateur d'URLs Symfony
     * @param VehiculeStatusService $vehiculeStatusService Service de gestion des statuts véhicule
     * @param EmailService $emailService Service d'envoi d'emails
     */
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
     * Crée une session de paiement Stripe Checkout pour une commande
     * 
     * Cette méthode configure une session de paiement complète avec :
     * - Les détails du produit (véhicule acheté)
     * - L'image du véhicule si disponible
     * - Les URLs de succès et d'annulation
     * - Les métadonnées pour le suivi
     * - Le support Apple Pay et cartes bancaires
     * - La collecte d'adresses de facturation et livraison
     * 
     * @param Orders $order Commande à payer
     * @param string $baseUrl URL de base de l'application
     * 
     * @return array Tableau contenant l'URL de la session de paiement
     * 
     * @throws \Stripe\Exception\ApiErrorException En cas d'erreur Stripe
     */
    public function createCheckoutSession(Orders $order, string $baseUrl): array
    {
        // Récupère le véhicule de la commande
        $vehicule = $order->getVehicules()->first();
        $vehiculeImage = null;
        
        // Récupère l'image du véhicule si disponible
        if ($vehicule && $vehicule->getPictures()->count() > 0) {
            $vehiculeImage = $baseUrl . '/uploads/vehicules/' . $vehicule->getPictures()->first()->getName();
        }

        // Configuration complète de la session de paiement
        $sessionConfig = [
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur', // Devise en euros
                    'product_data' => [
                        'name' => 'Commande #' . $order->getId(),
                        'description' => 'Paiement de commande',
                        'images' => $vehiculeImage ? [$vehiculeImage] : [], // Image du véhicule
                    ],
                    'unit_amount' => (int)($order->getTotalPrice() * 100), // Montant en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment', // Mode paiement unique
            'success_url' => $this->urlGenerator->generate('app_payment_success', [
                'session_id' => '{CHECKOUT_SESSION_ID}',
                'order_id' => $order->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_payment_cancel', [
                'order_id' => $order->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'order_id' => $order->getId(), // Métadonnées pour suivi
                'user_id' => $order->getUsers()->getId(),
            ],
            'payment_method_types' => ['card', 'apple_pay'], // Méthodes de paiement supportées
            'payment_method_options' => [
                'apple_pay' => [
                    'enabled' => true, // Activation d'Apple Pay
                ],
            ],
            'billing_address_collection' => 'required', // Collecte adresse de facturation
            'shipping_address_collection' => [
                'allowed_countries' => ['FR', 'BE', 'CH', 'CA'], // Pays autorisés pour livraison
            ],
        ];

        // Création de la session Stripe
        $checkoutSession = $this->stripeClient->checkout->sessions->create($sessionConfig);
        
        return ['url' => $checkoutSession->url]; // Retourne l'URL de paiement
    }

    /**
     * Crée un PaymentIntent pour Apple Pay
     * 
     * Cette méthode crée un PaymentIntent Stripe spécifiquement
     * pour les paiements Apple Pay. Le PaymentIntent permet
     * de gérer les paiements côté client de manière sécurisée.
     * 
     * @param Orders $order Commande à payer
     * 
     * @return array Tableau contenant le client_secret et les détails du paiement
     * 
     * @throws \Stripe\Exception\ApiErrorException En cas d'erreur Stripe
     */
    public function createPaymentIntent(Orders $order): array
    {
        // Création du PaymentIntent pour Apple Pay
        $paymentIntent = $this->stripeClient->paymentIntents->create([
            'amount' => (int)($order->getTotalPrice() * 100), // Montant en centimes
            'currency' => 'eur', // Devise en euros
            'automatic_payment_methods' => [
                'enabled' => true, // Activation des méthodes de paiement automatiques
            ],
            'metadata' => [
                'order_id' => $order->getId(), // Métadonnées pour suivi
                'user_id' => $order->getUsers()->getId(),
            ],
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret, // Secret pour le client
            'amount' => $paymentIntent->amount, // Montant du paiement
            'currency' => $paymentIntent->currency, // Devise
        ];
    }

    /**
     * Traite le succès d'un paiement
     * 
     * Cette méthode est appelée après un paiement réussi et effectue :
     * - La vérification du statut de paiement Stripe
     * - La mise à jour du statut de la commande
     * - Le marquage du véhicule comme vendu
     * - L'enregistrement du paiement en base
     * - L'envoi des emails de confirmation
     * - La gestion des erreurs avec logging
     * 
     * @param string $sessionId ID de la session Stripe
     * @param int $orderId ID de la commande
     * 
     * @throws \Stripe\Exception\ApiErrorException En cas d'erreur Stripe
     * @throws \Doctrine\ORM\ORMException En cas d'erreur de base de données
     */
    public function processPaymentSuccess(string $sessionId, int $orderId): void
    {
        // Récupération de la session Stripe pour vérification
        $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
        
        // Vérification que le paiement est bien effectué
        if ($session->payment_status === 'paid') {
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            
            if ($order) {
                // Mise à jour du statut de la commande
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
                
                // Création d'un enregistrement de paiement
                $payment = new Payement();
                $payment->setOrders($order);
                $payment->setAmount($order->getTotalPrice());
                $payment->setPayementStatus('completed');
                $payment->setPayementAt(new \DateTimeImmutable());
                
                // Persistance en base de données
                $this->entityManager->persist($payment);
                $this->entityManager->flush();
                
                // Envoi des emails de confirmation avec gestion d'erreur
                try {
                    $user = $order->getUsers();
                    $this->emailService->sendPurchaseConfirmation($order, $user);
                    $this->emailService->sendAdminNotification($order, $user);
                } catch (\Exception $emailException) {
                    // Log de l'erreur d'email sans interrompre le processus
                    $this->logger->error('Erreur lors de l\'envoi des emails de confirmation', [
                        'error' => $emailException->getMessage(),
                        'order_id' => $order->getId(),
                    ]);
                }
            }
        }
    }
} 