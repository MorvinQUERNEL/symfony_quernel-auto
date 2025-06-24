<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Vehicules;
use App\Form\OrderType;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[Route('/orders')]
class OrderController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_orders_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $ordersRepository->findAllWithRelations(),
        ]);
    }

    #[Route('/my-orders', name: 'app_orders_my_orders', methods: ['GET'])]
    public function myOrders(OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos commandes.');
            return $this->redirectToRoute('app_login');
        }

        $orders = $ordersRepository->findByUserWithRelations($user);

        return $this->render('order/my_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/new', name: 'app_orders_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Orders();
        $order->setCreatedAt(new \DateTimeImmutable());
        
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'La commande a été créée avec succès !');
            return $this->redirectToRoute('app_orders_index');
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/new-with-vehicule/{vehicule_id}', name: 'app_orders_new_with_vehicule', methods: ['GET', 'POST'])]
    public function newWithVehicule(Request $request, EntityManagerInterface $entityManager, int $vehicule_id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un véhicule.');
            return $this->redirectToRoute('app_login');
        }

        $vehicule = $entityManager->getRepository(\App\Entity\Vehicules::class)->find($vehicule_id);
        if (!$vehicule) {
            $this->addFlash('error', 'Véhicule non trouvé.');
            return $this->redirectToRoute('app_vehicules_index');
        }

        if ($vehicule->getStatus() !== 'Disponible') {
            $this->addFlash('error', 'Ce véhicule n\'est plus disponible.');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        // Créer une nouvelle commande avec les données pré-remplies
        $order = new Orders();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUsers($user);
        $order->setOrderStatus('pending');
        $order->setTotalPrice($vehicule->getSalePrice());
        $order->addVehicule($vehicule);
        
        // Pré-remplir les informations de livraison avec les données de l'utilisateur
        $order->setDeliveryCity($user->getCity() ?? '');
        $order->setDeliveryPostalCode($user->getPostalCode() ?? 0);
        $order->setDeliveryCountry($user->getCountry() ?? 'France');
        $order->setDeliveryAdress($user->getAddress() ?? '');
        
        $form = $this->createForm(\App\Form\PurchaseOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            // Créer la session de paiement Stripe
            return $this->redirectToRoute('app_orders_create_stripe_session', ['order_id' => $order->getId()]);
        }

        return $this->render('order/new_with_vehicule.html.twig', [
            'form' => $form,
            'vehicule' => $vehicule,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    #[Route('/create-stripe-session/{order_id}', name: 'app_orders_create_stripe_session', methods: ['GET'])]
    public function createStripeSession(int $order_id, EntityManagerInterface $entityManager, StripeClient $stripeClient): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        $order = $entityManager->getRepository(Orders::class)->find($order_id);
        if (!$order || $order->getUsers() !== $user) {
            $this->addFlash('error', 'Commande non trouvée ou accès non autorisé.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        if ($order->getOrderStatus() !== 'pending') {
            $this->addFlash('error', 'Cette commande ne peut plus être payée.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        try {
            // Récupérer le premier véhicule de la commande
            $vehicule = $order->getVehicules()->first();
            if (!$vehicule) {
                throw new \Exception('Aucun véhicule associé à cette commande.');
            }

            // Vérifier la limite de Stripe (999 999,99 € = 99 999 999 centimes)
            $priceInCents = $vehicule->getSalePrice();
            $priceInEuros = $priceInCents / 100;
            
            if ($priceInEuros > 999999.99) {
                return $this->render('payment/limit_exceeded.html.twig', [
                    'order' => $order,
                ]);
            }

            // Créer la session de paiement Stripe
            $checkoutSession = $stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $vehicule->getBrand() . ' ' . $vehicule->getModel(),
                            'description' => $vehicule->getDescription() ?? 'Achat de véhicule',
                            'images' => $vehicule->getPictures()->count() > 0 
                                ? [$this->generateUrl('app_home', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL) . 'uploads/vehicules/' . $vehicule->getPictures()->first()->getName()]
                                : [],
                        ],
                        'unit_amount' => $priceInCents, // Le prix est déjà en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_payment_cancel', ['order_id' => $order->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'order_id' => $order->getId(),
                    'user_id' => $user->getId(),
                    'vehicule_id' => $vehicule->getId(),
                ],
            ]);

            // Rediriger vers Stripe Checkout
            return $this->redirect($checkoutSession->url, 303);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_orders_my_orders');
        }
    }

    #[Route('/{id}', name: 'app_orders_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Orders $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_orders_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La commande a été modifiée avec succès !');
            return $this->redirectToRoute('app_orders_show', ['id' => $order->getId()]);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur est propriétaire de la commande ou admin
        if ($order->getUsers() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette commande.');
            return $this->redirectToRoute('app_orders_my_orders');
        }
        
        // Vérifier que la commande peut être supprimée (seulement pending ou expired)
        if (!in_array($order->getOrderStatus(), ['pending', 'expired'])) {
            $this->addFlash('error', 'Cette commande ne peut pas être supprimée.');
            return $this->redirectToRoute('app_orders_my_orders');
        }
        
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            try {
                // Récupérer tous les véhicules liés à cette commande
                $vehicules = $order->getVehicules();
                
                // Dissocier les véhicules de la commande et les remettre en statut "Disponible"
                foreach ($vehicules as $vehicule) {
                    $vehicule->setOrders(null); // Dissocier de la commande
                    $vehicule->setStatus('Disponible'); // Remettre en disponibilité
                    $entityManager->persist($vehicule);
                }
                
                // Supprimer la commande
                $entityManager->remove($order);
                $entityManager->flush();
                
                $this->addFlash('success', 'La commande a été supprimée avec succès.');
                
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la suppression de la commande', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->getId(),
                    'user_id' => $user->getId(),
                ]);
                
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la commande.');
            }
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }
        
        return $this->redirectToRoute('app_orders_my_orders');
    }

    #[Route('/orders/create-with-apple-pay', name: 'app_orders_create_with_apple_pay', methods: ['POST'])]
    public function createWithApplePay(Request $request, EntityManagerInterface $entityManager, StripeClient $stripeClient): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('Vous devez être connecté pour acheter un véhicule.');
            }

            $data = json_decode($request->getContent(), true);
            $vehiculeId = $data['vehicule_id'] ?? null;
            $paymentMethodId = $data['payment_method'] ?? null;
            $shippingAddress = $data['shipping_address'] ?? null;
            $deliveryAddress = $data['delivery_address'] ?? null;

            if (!$vehiculeId) {
                throw new \Exception('ID du véhicule manquant.');
            }

            $vehicule = $entityManager->getRepository(\App\Entity\Vehicules::class)->find($vehiculeId);
            if (!$vehicule) {
                throw new \Exception('Véhicule non trouvé.');
            }

            if ($vehicule->getStatus() !== 'Disponible') {
                throw new \Exception('Ce véhicule n\'est plus disponible.');
            }

            // Créer une nouvelle commande
            $order = new Orders();
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUsers($user);
            $order->setOrderStatus('pending');
            $order->setTotalPrice($vehicule->getSalePrice());
            $order->addVehicule($vehicule);
            
            // Pré-remplir les informations de livraison
            if ($deliveryAddress) {
                $order->setDeliveryAdress($deliveryAddress['line1'] ?? '');
                $order->setDeliveryCity($deliveryAddress['city'] ?? '');
                $order->setDeliveryPostalCode($deliveryAddress['postal_code'] ?? 0);
                $order->setDeliveryCountry($deliveryAddress['country'] ?? 'France');
            } else {
                // Utiliser les données de l'utilisateur par défaut
                $order->setDeliveryCity($user->getCity() ?? '');
                $order->setDeliveryPostalCode($user->getPostalCode() ?? 0);
                $order->setDeliveryCountry($user->getCountry() ?? 'France');
                $order->setDeliveryAdress($user->getAddress() ?? '');
            }

            // Persister la commande
            $entityManager->persist($order);
            $entityManager->flush();

            // Créer le PaymentIntent pour Apple Pay
            $paymentIntent = $stripeClient->paymentIntents->create([
                'amount' => $vehicule->getSalePrice(), // déjà en centimes
                'currency' => 'eur',
                'payment_method' => $paymentMethodId,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'order_id' => $order->getId(),
                    'user_id' => $user->getId(),
                    'vehicule_id' => $vehicule->getId(),
                ],
                'description' => 'Achat ' . $vehicule->getBrand() . ' ' . $vehicule->getModel(),
            ]);

            return new JsonResponse([
                'client_secret' => $paymentIntent->client_secret,
                'order_id' => $order->getId(),
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de commande avec Apple Pay', [
                'error' => $e->getMessage(),
                'vehicule_id' => $vehiculeId ?? null,
                'user_id' => $user->getId() ?? null,
            ]);

            return new JsonResponse([
                'error' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 400);
        }
    }
} 