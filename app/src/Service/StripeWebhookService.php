<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Payement;
use App\Service\EmailService;
use App\Service\VehiculeStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;

class StripeWebhookService
{
    private StripeClient $stripeClient;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EmailService $emailService;
    private VehiculeStatusService $vehiculeStatusService;

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

    public function processWebhookEvent(string $eventType, object $eventData): void
    {
        $this->logger->info('Traitement d\'un événement webhook Stripe', [
            'event_type' => $eventType,
        ]);

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

    private function handleCheckoutSessionCompleted($session): void
    {
        try {
            $orderId = $session->metadata->order_id ?? null;
            $userId = $session->metadata->user_id ?? null;
            $vehiculeId = $session->metadata->vehicule_id ?? null;
            
            if (!$orderId) {
                $this->logger->warning('Webhook checkout.session.completed sans order_id', [
                    'session_id' => $session->id,
                ]);
                return;
            }

            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            if (!$order) {
                $this->logger->error('Commande non trouvée pour le webhook', [
                    'order_id' => $orderId,
                    'session_id' => $session->id,
                ]);
                return;
            }

            if ($order->getOrderStatus() === 'pending') {
                $order->setOrderStatus('paid');
                
                // Marquer le véhicule comme vendu
                $this->vehiculeStatusService->markVehiculeAsSold($order, $vehiculeId);
                
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
                    
                    $this->logger->info('Emails de confirmation envoyés avec succès', [
                        'order_id' => $orderId,
                        'user_id' => $user->getId(),
                    ]);
                } catch (\Exception $emailException) {
                    $this->logger->error('Erreur lors de l\'envoi des emails de confirmation', [
                        'error' => $emailException->getMessage(),
                        'order_id' => $orderId,
                    ]);
                }
                
                $this->logger->info('Commande payée avec succès via webhook', [
                    'order_id' => $orderId,
                    'session_id' => $session->id,
                    'payment_intent_id' => $session->payment_intent,
                    'vehicule_id' => $vehiculeId,
                ]);
            } else {
                $this->logger->info('Commande déjà traitée', [
                    'order_id' => $orderId,
                    'status' => $order->getOrderStatus(),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du webhook checkout.session.completed', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
            throw $e;
        }
    }

    private function handleCheckoutSessionExpired($session): void
    {
        try {
            $orderId = $session->metadata->order_id ?? null;
            if ($orderId) {
                $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
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

    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $this->logger->info('Paiement réussi via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
        ]);
    }

    private function handlePaymentIntentFailed($paymentIntent): void
    {
        $this->logger->warning('Paiement échoué via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error->message ?? 'Erreur inconnue',
            'status' => $paymentIntent->status,
        ]);
    }

    private function handlePaymentIntentCanceled($paymentIntent): void
    {
        $this->logger->info('Paiement annulé via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);
    }

    private function handleInvoicePaymentSucceeded($invoice): void
    {
        $this->logger->info('Facture payée avec succès via webhook', [
            'invoice_id' => $invoice->id,
            'amount_paid' => $invoice->amount_paid,
            'customer_id' => $invoice->customer,
        ]);
    }

    private function handleInvoicePaymentFailed($invoice): void
    {
        $this->logger->warning('Échec de paiement de facture via webhook', [
            'invoice_id' => $invoice->id,
            'amount_due' => $invoice->amount_due,
            'customer_id' => $invoice->customer,
        ]);
    }

    public function getSupportedEvents(): array
    {
        return [
            'checkout.session.completed',
            'checkout.session.expired',
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'payment_intent.canceled',
            'invoice.payment_succeeded',
            'invoice.payment_failed'
        ];
    }
} 