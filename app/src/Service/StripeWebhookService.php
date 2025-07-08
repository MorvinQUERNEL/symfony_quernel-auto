<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Payement;
use App\Service\EmailService;
use App\Service\VehiculeStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;

/**
 * Service de gestion des webhooks Stripe
 * 
 * Ce service gère :
 * - Le traitement des événements webhook Stripe en temps réel
 * - La mise à jour automatique des statuts de commande
 * - Le marquage des véhicules comme vendus après paiement
 * - L'enregistrement des paiements en base de données
 * - L'envoi d'emails de confirmation automatiques
 * - La gestion des sessions de paiement expirées
 * - Le logging détaillé de tous les événements
 * - La gestion des erreurs de paiement
 * 
 * Assure la synchronisation entre Stripe et l'application
 * pour une gestion fiable des paiements.
 */
class StripeWebhookService
{
    private StripeClient $stripeClient;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EmailService $emailService;
    private VehiculeStatusService $vehiculeStatusService;

    /**
     * Constructeur du service de webhooks Stripe
     * 
     * @param StripeClient $stripeClient Client Stripe configuré
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param LoggerInterface $logger Service de logging
     * @param EmailService $emailService Service d'envoi d'emails
     * @param VehiculeStatusService $vehiculeStatusService Service de gestion des statuts véhicule
     */
    public function __construct(
        StripeClient $stripeClient,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EmailService $emailService,
        VehiculeStatusService $vehiculeStatusService
    ) {
        $this->stripeClient = $stripeClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->emailService = $emailService;
        $this->vehiculeStatusService = $vehiculeStatusService;
    }

    /**
     * Traite un événement webhook Stripe
     * 
     * Cette méthode est le point d'entrée principal pour le traitement
     * des webhooks Stripe. Elle route l'événement vers le gestionnaire
     * approprié selon le type d'événement reçu.
     * 
     * Les événements supportés sont :
     * - checkout.session.completed : Session de paiement terminée avec succès
     * - checkout.session.expired : Session de paiement expirée
     * - payment_intent.succeeded : Paiement réussi
     * - payment_intent.payment_failed : Échec de paiement
     * - payment_intent.canceled : Paiement annulé
     * - invoice.payment_succeeded : Facture payée
     * - invoice.payment_failed : Échec de paiement de facture
     * 
     * @param string $eventType Type d'événement Stripe reçu
     * @param object $eventData Données de l'événement Stripe
     * 
     * @throws \Exception En cas d'erreur lors du traitement
     */
    public function processWebhookEvent(string $eventType, object $eventData): void
    {
        // Logging de l'événement reçu
        $this->logger->info('Traitement d\'un événement webhook Stripe', [
            'event_type' => $eventType,
        ]);

        // Routage vers le gestionnaire approprié
        switch ($eventType) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($eventData);
                break;
            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($eventData);
                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($eventData);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($eventData);
                break;
            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($eventData);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($eventData);
                break;
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($eventData);
                break;
            default:
                $this->logger->info('Événement Stripe non géré', ['event_type' => $eventType]);
                break;
        }
    }

    /**
     * Gère l'événement de session de paiement terminée avec succès
     * 
     * Cette méthode traite l'événement le plus important : une session
     * de paiement Stripe Checkout qui s'est terminée avec succès.
     * Elle effectue :
     * - La récupération des métadonnées de la session
     * - La mise à jour du statut de la commande
     * - Le marquage du véhicule comme vendu
     * - L'enregistrement du paiement en base
     * - L'envoi des emails de confirmation
     * - Le logging détaillé de l'opération
     * 
     * @param object $session Objet session Stripe avec les données de paiement
     * 
     * @throws \Exception En cas d'erreur lors du traitement
     */
    private function handleCheckoutSessionCompleted($session): void
    {
        try {
            // Récupération des métadonnées de la session
            $orderId = $session->metadata->order_id ?? null;
            $userId = $session->metadata->user_id ?? null;
            $vehiculeId = $session->metadata->vehicule_id ?? null;
            
            // Vérification de la présence de l'ID de commande
            if (!$orderId) {
                $this->logger->warning('Webhook checkout.session.completed sans order_id', [
                    'session_id' => $session->id,
                ]);
                return;
            }

            // Récupération de la commande depuis la base de données
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            if (!$order) {
                $this->logger->error('Commande non trouvée pour le webhook', [
                    'order_id' => $orderId,
                    'session_id' => $session->id,
                ]);
                return;
            }

            // Traitement uniquement si la commande est en attente
            if ($order->getOrderStatus() === 'pending') {
                // Mise à jour du statut de la commande
                $order->setOrderStatus('paid');
                
                // Marquer le véhicule comme vendu
                $this->vehiculeStatusService->markVehiculeAsSold($order, $vehiculeId);
                
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
                    
                    $this->logger->info('Emails de confirmation envoyés avec succès', [
                        'order_id' => $orderId,
                        'user_id' => $user->getId(),
                    ]);
                } catch (\Exception $emailException) {
                    // Log de l'erreur d'email sans interrompre le processus
                    $this->logger->error('Erreur lors de l\'envoi des emails de confirmation', [
                        'error' => $emailException->getMessage(),
                        'order_id' => $orderId,
                    ]);
                }
                
                // Logging du succès complet
                $this->logger->info('Commande payée avec succès via webhook', [
                    'order_id' => $orderId,
                    'session_id' => $session->id,
                    'payment_intent_id' => $session->payment_intent,
                    'vehicule_id' => $vehiculeId,
                ]);
            } else {
                // Logging si la commande a déjà été traitée
                $this->logger->info('Commande déjà traitée', [
                    'order_id' => $orderId,
                    'status' => $order->getOrderStatus(),
                ]);
            }
        } catch (\Exception $e) {
            // Logging de l'erreur avec contexte détaillé
            $this->logger->error('Erreur lors du traitement du webhook checkout.session.completed', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
            throw $e; // Remontée de l'erreur pour gestion externe
        }
    }

    /**
     * Gère l'événement de session de paiement expirée
     * 
     * Cette méthode traite les sessions de paiement qui ont expiré
     * sans avoir été complétées. Elle met à jour le statut de la
     * commande correspondante vers "expired".
     * 
     * @param object $session Objet session Stripe expirée
     */
    private function handleCheckoutSessionExpired($session): void
    {
        try {
            $orderId = $session->metadata->order_id ?? null;
            if ($orderId) {
                $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
                // Mise à jour uniquement si la commande est encore en attente
                if ($order && $order->getOrderStatus() === 'pending') {
                    $order->setOrderStatus('expired');
                    $this->entityManager->flush();
                    
                    $this->logger->info('Session de paiement expirée', [
                        'order_id' => $orderId,
                        'session_id' => $session->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du webhook checkout.session.expired', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
        }
    }

    /**
     * Gère l'événement de paiement réussi
     * 
     * Cette méthode traite les PaymentIntents qui ont réussi.
     * Elle effectue principalement du logging pour le suivi.
     * 
     * @param object $paymentIntent Objet PaymentIntent Stripe
     */
    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $this->logger->info('Paiement réussi via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
        ]);
    }

    /**
     * Gère l'événement d'échec de paiement
     * 
     * Cette méthode traite les PaymentIntents qui ont échoué.
     * Elle effectue du logging avec les détails de l'erreur.
     * 
     * @param object $paymentIntent Objet PaymentIntent Stripe en échec
     */
    private function handlePaymentIntentFailed($paymentIntent): void
    {
        $this->logger->warning('Paiement échoué via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error->message ?? 'Erreur inconnue',
            'status' => $paymentIntent->status,
        ]);
    }

    /**
     * Gère l'événement de paiement annulé
     * 
     * Cette méthode traite les PaymentIntents qui ont été annulés.
     * Elle effectue du logging pour le suivi.
     * 
     * @param object $paymentIntent Objet PaymentIntent Stripe annulé
     */
    private function handlePaymentIntentCanceled($paymentIntent): void
    {
        $this->logger->info('Paiement annulé via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);
    }

    /**
     * Gère l'événement de facture payée avec succès
     * 
     * Cette méthode traite les factures Stripe qui ont été payées.
     * Elle effectue du logging pour le suivi.
     * 
     * @param object $invoice Objet facture Stripe payée
     */
    private function handleInvoicePaymentSucceeded($invoice): void
    {
        $this->logger->info('Facture payée avec succès via webhook', [
            'invoice_id' => $invoice->id,
            'amount_paid' => $invoice->amount_paid,
            'customer_id' => $invoice->customer,
        ]);
    }

    /**
     * Gère l'événement d'échec de paiement de facture
     * 
     * Cette méthode traite les factures Stripe dont le paiement a échoué.
     * Elle effectue du logging avec les détails de l'erreur.
     * 
     * @param object $invoice Objet facture Stripe en échec
     */
    private function handleInvoicePaymentFailed($invoice): void
    {
        $this->logger->warning('Échec de paiement de facture via webhook', [
            'invoice_id' => $invoice->id,
            'amount_due' => $invoice->amount_due,
            'customer_id' => $invoice->customer,
        ]);
    }

    /**
     * Retourne la liste des événements webhook supportés
     * 
     * Cette méthode fournit la liste complète des types d'événements
     * Stripe que ce service peut traiter. Utile pour la configuration
     * des webhooks côté Stripe.
     * 
     * @return array Liste des types d'événements supportés
     */
    public function getSupportedEvents(): array
    {
        return [
            'checkout.session.completed',    // Session de paiement terminée
            'checkout.session.expired',      // Session de paiement expirée
            'payment_intent.succeeded',      // Paiement réussi
            'payment_intent.payment_failed', // Échec de paiement
            'payment_intent.canceled',       // Paiement annulé
            'invoice.payment_succeeded',     // Facture payée
            'invoice.payment_failed'         // Échec de paiement de facture
        ];
    }
} 