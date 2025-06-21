<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Form\OrderType;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\StripeClient;

#[Route('/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_orders_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $ordersRepository->findAll(),
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

        $orders = $ordersRepository->findBy(['users' => $user], ['createdAt' => 'DESC']);

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
    public function newWithVehicule(Request $request, int $vehicule_id, EntityManagerInterface $entityManager): Response
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
            'order' => $order,
            'form' => $form,
            'vehicule' => $vehicule,
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
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
            
            $this->addFlash('success', 'La commande a été supprimée avec succès !');
        }

        return $this->redirectToRoute('app_orders_index');
    }
} 