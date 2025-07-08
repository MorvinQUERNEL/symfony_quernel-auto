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
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Contrôleur gérant les commandes de véhicules
 * 
 * Ce contrôleur gère :
 * - L'affichage et gestion des commandes (admin et utilisateurs)
 * - La création de commandes avec véhicules
 * - L'intégration avec Stripe pour les paiements
 * - Le support d'Apple Pay
 * - La gestion des statuts de commande
 * - L'envoi d'emails de notification
 */
#[Route('/orders')]
class OrderController extends AbstractController
{
    private LoggerInterface $logger;

    /**
     * Constructeur avec injection du service de logging
     * 
     * @param LoggerInterface $logger Service de logging pour tracer les erreurs
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Liste de toutes les commandes (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage de toutes les commandes avec leurs relations
     * - L'accès restreint aux administrateurs uniquement
     * 
     * @param OrdersRepository $ordersRepository Repository des commandes
     * @return Response Page de liste des commandes
     */
    #[Route('/', name: 'app_orders_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $ordersRepository->findAllWithRelations(),
        ]);
    }

    /**
     * Liste des commandes de l'utilisateur connecté
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification
     * - L'affichage des commandes de l'utilisateur connecté
     * - La redirection vers la connexion si non authentifié
     * 
     * @param OrdersRepository $ordersRepository Repository des commandes
     * @return Response Page des commandes de l'utilisateur ou redirection
     */
    #[Route('/my-orders', name: 'app_orders_my_orders', methods: ['GET'])]
    public function myOrders(OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();
        
        // Vérification de l'authentification
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos commandes.');
            return $this->redirectToRoute('app_login');
        }

        // Récupération des commandes de l'utilisateur avec leurs relations
        $orders = $ordersRepository->findByUserWithRelations($user);

        return $this->render('order/my_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * Création d'une nouvelle commande (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de création de commande
     * - Le traitement du formulaire avec validation
     * - La persistance de la commande en base de données
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Formulaire de création ou redirection après succès
     */
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

    /**
     * Création d'une commande avec un véhicule spécifique
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification et de la disponibilité du véhicule
     * - La pré-remplissage des informations de commande
     * - La création d'une commande avec le véhicule sélectionné
     * - La redirection vers le processus de paiement
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param int $vehicule_id ID du véhicule à commander
     * @return Response Formulaire de commande ou redirection
     */
    #[Route('/new-with-vehicule/{vehicule_id}', name: 'app_orders_new_with_vehicule', methods: ['GET', 'POST'])]
    public function newWithVehicule(Request $request, EntityManagerInterface $entityManager, int $vehicule_id): Response
    {
        $user = $this->getUser();
        
        // Vérification de l'authentification
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un véhicule.');
            return $this->redirectToRoute('app_login');
        }

        // Récupération et vérification du véhicule
        $vehicule = $entityManager->getRepository(\App\Entity\Vehicules::class)->find($vehicule_id);
        if (!$vehicule) {
            $this->addFlash('error', 'Véhicule non trouvé.');
            return $this->redirectToRoute('app_vehicules_index');
        }

        // Vérification de la disponibilité du véhicule
        if ($vehicule->getStatus() !== 'Disponible') {
            $this->addFlash('error', 'Ce véhicule n\'est plus disponible.');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        // Création d'une nouvelle commande avec les données pré-remplies
        $order = new Orders();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUsers($user);
        $order->setOrderStatus('pending');
        $order->setTotalPrice($vehicule->getSalePrice());
        $order->addVehicule($vehicule);

        // Pré-remplissage des informations de livraison avec les données de l'utilisateur
        $order->setDeliveryCity($user->getCity() ?? '');
        $order->setDeliveryPostalCode($user->getPostalCode() ?? 0);
        $order->setDeliveryCountry($user->getCountry() ?? 'France');
        $order->setDeliveryAdress($user->getAddress() ?? '');

        $form = $this->createForm(\App\Form\PurchaseOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            // Redirection vers la création de session Stripe
            return $this->redirectToRoute('app_orders_create_stripe_session', ['order_id' => $order->getId()]);
        }

        return $this->render('order/new_with_vehicule.html.twig', [
            'form' => $form,
            'vehicule' => $vehicule,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    /**
     * Création d'une session de paiement Stripe
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification et des autorisations
     * - La vérification des limites de prix Stripe
     * - La création d'une session de paiement Stripe
     * - La redirection vers Stripe Checkout
     * 
     * @param int $order_id ID de la commande
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param StripeClient $stripeClient Client Stripe
     * @return Response Redirection vers Stripe ou page d'erreur
     */
    #[Route('/create-stripe-session/{order_id}', name: 'app_orders_create_stripe_session', methods: ['GET'])]
    public function createStripeSession(int $order_id, EntityManagerInterface $entityManager, StripeClient $stripeClient): Response
    {
        $user = $this->getUser();
        
        // Vérification de l'authentification
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        // Récupération et vérification de la commande
        $order = $entityManager->getRepository(Orders::class)->find($order_id);
        if (!$order || $order->getUsers() !== $user) {
            $this->addFlash('error', 'Commande non trouvée ou accès non autorisé.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        // Vérification du statut de la commande
        if ($order->getOrderStatus() !== 'pending') {
            $this->addFlash('error', 'Cette commande ne peut plus être payée.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        try {
            // Récupération du véhicule associé à la commande
            $vehicule = $order->getVehicules()->first();
            if (!$vehicule) {
                throw new \Exception('Aucun véhicule associé à cette commande.');
            }

            // Vérification de la limite de prix Stripe (999 999,99 € = 99 999 999 centimes)
            $priceInCents = $vehicule->getSalePrice();
            $priceInEuros = $priceInCents / 100;

            if ($priceInEuros > 9999999.99) {
                return $this->render('payment/limit_exceeded.html.twig', [
                    'order' => $order,
                ]);
            }

            // Création de la session de paiement Stripe
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

            // Redirection vers Stripe Checkout
            return $this->redirect($checkoutSession->url, 303);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_orders_my_orders');
        }
    }

    /**
     * Affichage détaillé d'une commande (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage des détails complets d'une commande
     * - L'accès restreint aux administrateurs uniquement
     * 
     * @param Orders $order Commande à afficher
     * @return Response Page de détail de la commande
     */
    #[Route('/{id}', name: 'app_orders_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Orders $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * Modification d'une commande (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de modification
     * - Le traitement des modifications avec validation
     * - L'envoi d'emails de notification en cas de changement de statut
     * - La mise à jour de la commande en base de données
     * 
     * @param Request $request Requête HTTP
     * @param Orders $order Commande à modifier
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @return Response Formulaire de modification ou redirection après succès
     */
    #[Route('/{id}/edit', name: 'app_orders_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Orders $order, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $oldStatus = $order->getOrderStatus();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Vérification du changement de statut et envoi d'email de notification
            $newStatus = $order->getOrderStatus();
            if ($newStatus !== $oldStatus) {
                $user = $order->getUsers();
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@quernel-auto.fr', 'Quernel Auto'))
                    ->to($user->getEmail())
                    ->subject('Mise à jour de votre commande #' . $order->getId())
                    ->htmlTemplate('emails/order_status_update.html.twig')
                    ->context([
                        'user' => $user,
                        'order' => $order,
                    ]);

                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    // Log l'erreur sans bloquer le processus
                }
            }

            $this->addFlash('success', 'La commande a été modifiée avec succès !');

            return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * Suppression d'une commande
     * 
     * Cette méthode gère :
     * - La vérification des autorisations (propriétaire ou admin)
     * - La vérification du statut de la commande (supprimable uniquement si pending ou expired)
     * - La remise en disponibilité des véhicules associés
     * - La suppression de la commande de la base de données
     * - La validation du token CSRF
     * 
     * @param Request $request Requête HTTP
     * @param Orders $order Commande à supprimer
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la liste des commandes
     */
    #[Route('/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérification de l'authentification
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        // Vérification des autorisations (propriétaire ou admin)
        if ($order->getUsers() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette commande.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        // Vérification du statut de la commande (supprimable uniquement si pending ou expired)
        if (!in_array($order->getOrderStatus(), ['pending', 'expired'])) {
            $this->addFlash('error', 'Cette commande ne peut pas être supprimée.');
            return $this->redirectToRoute('app_orders_my_orders');
        }

        // Validation du token CSRF
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            try {
                // Récupération de tous les véhicules liés à cette commande
                $vehicules = $order->getVehicules();

                // Dissociation des véhicules de la commande et remise en disponibilité
                foreach ($vehicules as $vehicule) {
                    $vehicule->setOrders(null); // Dissocier de la commande
                    $vehicule->setStatus('Disponible'); // Remettre en disponibilité
                    $entityManager->persist($vehicule);
                }

                // Suppression de la commande
                $entityManager->remove($order);
                $entityManager->flush();

                $this->addFlash('success', 'La commande a été supprimée avec succès.');

            } catch (\Exception $e) {
                // Logging de l'erreur pour le debugging
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

    /**
     * Création d'une commande avec Apple Pay
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification
     * - La validation des données de paiement Apple Pay
     * - La création d'une commande avec les informations de livraison
     * - La création d'un PaymentIntent Stripe pour Apple Pay
     * - Le retour des informations nécessaires au frontend
     * 
     * @param Request $request Requête HTTP contenant les données Apple Pay
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param StripeClient $stripeClient Client Stripe
     * @return JsonResponse Réponse JSON avec les informations de paiement
     */
    #[Route('/orders/create-with-apple-pay', name: 'app_orders_create_with_apple_pay', methods: ['POST'])]
    public function createWithApplePay(Request $request, EntityManagerInterface $entityManager, StripeClient $stripeClient): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            // Vérification de l'authentification
            if (!$user) {
                throw new \Exception('Vous devez être connecté pour acheter un véhicule.');
            }

            // Récupération et validation des données de la requête
            $data = json_decode($request->getContent(), true);
            $vehiculeId = $data['vehicule_id'] ?? null;
            $paymentMethodId = $data['payment_method'] ?? null;
            $shippingAddress = $data['shipping_address'] ?? null;
            $deliveryAddress = $data['delivery_address'] ?? null;

            if (!$vehiculeId) {
                throw new \Exception('ID du véhicule manquant.');
            }

            // Récupération et validation du véhicule
            $vehicule = $entityManager->getRepository(\App\Entity\Vehicules::class)->find($vehiculeId);
            if (!$vehicule) {
                throw new \Exception('Véhicule non trouvé.');
            }

            if ($vehicule->getStatus() !== 'Disponible') {
                throw new \Exception('Ce véhicule n\'est plus disponible.');
            }

            // Création d'une nouvelle commande
            $order = new Orders();
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUsers($user);
            $order->setOrderStatus('pending');
            $order->setTotalPrice($vehicule->getSalePrice());
            $order->addVehicule($vehicule);

            // Pré-remplissage des informations de livraison
            if ($deliveryAddress) {
                $order->setDeliveryAdress($deliveryAddress['line1'] ?? '');
                $order->setDeliveryCity($deliveryAddress['city'] ?? '');
                $order->setDeliveryPostalCode($deliveryAddress['postal_code'] ?? 0);
                $order->setDeliveryCountry($deliveryAddress['country'] ?? 'France');
            } else {
                // Utilisation des données de l'utilisateur par défaut
                $order->setDeliveryCity($user->getCity() ?? '');
                $order->setDeliveryPostalCode($user->getPostalCode() ?? 0);
                $order->setDeliveryCountry($user->getCountry() ?? 'France');
                $order->setDeliveryAdress($user->getAddress() ?? '');
            }

            // Persistance de la commande
            $entityManager->persist($order);
            $entityManager->flush();

            // Création du PaymentIntent pour Apple Pay
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
            // Logging de l'erreur pour le debugging
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
