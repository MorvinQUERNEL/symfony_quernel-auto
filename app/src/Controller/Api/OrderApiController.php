<?php

namespace App\Controller\Api;

use App\Entity\Orders;
use App\Entity\Users;
use App\Entity\Vehicules;
use App\Repository\OrdersRepository;
use App\Repository\VehiculesRepository;
use App\Service\StripeService;
use App\Service\VehiculeStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/orders')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class OrderApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrdersRepository $ordersRepository,
        private VehiculesRepository $vehiculesRepository,
    ) {
    }

    #[Route('', name: 'api_orders_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $orders = $this->ordersRepository->findBy(
            ['users' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->json(
            $orders,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:list']]
        );
    }

    #[Route('/{id}', name: 'api_orders_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $order = $this->ordersRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire ou admin
        if ($order->getUsers() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            $order,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']]
        );
    }

    #[Route('', name: 'api_orders_create', methods: ['POST'])]
    public function create(Request $request, VehiculeStatusService $statusService): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier les champs requis
        $requiredFields = ['vehiculeId', 'deliveryAddress', 'deliveryCity', 'deliveryPostalCode', 'deliveryCountry'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Récupérer le véhicule
        $vehicule = $this->vehiculesRepository->find($data['vehiculeId']);

        if (!$vehicule) {
            return $this->json(['error' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que le véhicule est disponible (accepte 'available' ou 'Disponible')
        $status = strtolower($vehicule->getStatus() ?? '');
        if ($status !== 'available' && $status !== 'disponible') {
            return $this->json(
                ['error' => 'Ce véhicule n\'est plus disponible'],
                Response::HTTP_CONFLICT
            );
        }

        // Créer la commande
        $order = new Orders();
        $order->setUsers($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setOrderStatus('pending');
        $order->setTotalPrice($vehicule->getSalePrice());
        $order->setDeliveryAdress($data['deliveryAddress']);
        $order->setDeliveryCity($data['deliveryCity']);
        $order->setDeliveryPostalCode((int) $data['deliveryPostalCode']);
        $order->setDeliveryCountry($data['deliveryCountry']);

        // Associer le véhicule à la commande
        $order->addVehicule($vehicule);

        // Marquer le véhicule comme réservé
        $vehicule->setStatus('reserved');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(
            $order,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['order:read']]
        );
    }

    #[Route('/{id}', name: 'api_orders_cancel', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function cancel(int $id): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $order = $this->ordersRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire
        if ($order->getUsers() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que la commande peut être annulée
        if ($order->getOrderStatus() !== 'pending') {
            return $this->json(
                ['error' => 'Seules les commandes en attente peuvent être annulées'],
                Response::HTTP_CONFLICT
            );
        }

        // Remettre le véhicule en disponible
        foreach ($order->getVehicules() as $vehicule) {
            $vehicule->setStatus('available');
        }

        $order->setOrderStatus('cancelled');
        $this->entityManager->flush();

        return $this->json(['message' => 'Commande annulée avec succès']);
    }

    #[Route('/{id}/checkout', name: 'api_orders_checkout', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function checkout(int $id, Request $request, StripeService $stripeService): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $order = $this->ordersRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire
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
            // Récupérer l'URL de base depuis la requête
            $baseUrl = $request->getSchemeAndHttpHost();

            $result = $stripeService->createCheckoutSession($order, $baseUrl);

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Erreur lors de la création de la session de paiement'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
