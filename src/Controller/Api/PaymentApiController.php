<?php

namespace App\Controller\Api;

use App\Entity\Orders;
use App\Entity\Users;
use App\Repository\OrdersRepository;
use App\Service\StripeService;
use App\Service\StripeWebhookService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/payments')]
class PaymentApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrdersRepository $ordersRepository,
        private StripeService $stripeService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/checkout-session', name: 'api_payments_checkout_session', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (empty($data['orderId'])) {
            return $this->json(['error' => "L'ID de commande est requis"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->ordersRepository->find($data['orderId']);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire de la commande
        if ($order->getUsers() !== $user) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que la commande est en attente
        if ($order->getOrderStatus() !== 'pending') {
            return $this->json(
                ['error' => 'Cette commande ne peut pas être payée'],
                Response::HTTP_CONFLICT
            );
        }

        try {
            $baseUrl = $request->getSchemeAndHttpHost();
            $result = $this->stripeService->createCheckoutSession($order, $baseUrl);

            return $this->json($result);
        } catch (\Exception $e) {
            $this->logger->error('Erreur création session Stripe', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
            ]);

            return $this->json(
                ['error' => 'Erreur lors de la création de la session de paiement'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/payment-intent', name: 'api_payments_payment_intent', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (empty($data['orderId'])) {
            return $this->json(['error' => "L'ID de commande est requis"], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->ordersRepository->find($data['orderId']);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire de la commande
        if ($order->getUsers() !== $user) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que la commande est en attente
        if ($order->getOrderStatus() !== 'pending') {
            return $this->json(
                ['error' => 'Cette commande ne peut pas être payée'],
                Response::HTTP_CONFLICT
            );
        }

        try {
            $result = $this->stripeService->createPaymentIntent($order);

            return $this->json($result);
        } catch (\Exception $e) {
            $this->logger->error('Erreur création PaymentIntent', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
            ]);

            return $this->json(
                ['error' => 'Erreur lors de la création du payment intent'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/webhook', name: 'api_payments_webhook', methods: ['POST'])]
    public function webhook(Request $request, StripeWebhookService $webhookService): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        try {
            $result = $webhookService->handleWebhook($payload, $sigHeader);

            return $this->json($result);
        } catch (\Exception $e) {
            $this->logger->error('Erreur webhook Stripe', [
                'error' => $e->getMessage(),
            ]);

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[Route('/status/{orderId}', name: 'api_payments_status', methods: ['GET'], requirements: ['orderId' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getPaymentStatus(int $orderId): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $order = $this->ordersRepository->find($orderId);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire de la commande
        if ($order->getUsers() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $payments = $order->getPayement();
        $latestPayment = $payments->last();

        return $this->json([
            'orderId' => $order->getId(),
            'orderStatus' => $order->getOrderStatus(),
            'totalPrice' => $order->getTotalPrice(),
            'payment' => $latestPayment ? [
                'status' => $latestPayment->getPayementStatus(),
                'amount' => $latestPayment->getAmount(),
                'paidAt' => $latestPayment->getPayementAt()?->format('c'),
            ] : null,
        ]);
    }
}
