<?php

namespace App\Controller\Api;

use App\Repository\MessagesRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminApiController extends AbstractController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private OrdersRepository $ordersRepository,
        private MessagesRepository $messagesRepository,
    ) {
    }

    #[Route('/users', name: 'api_admin_users', methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        $queryBuilder = $this->usersRepository->createQueryBuilder('u');

        if ($search) {
            $queryBuilder
                ->where('u.email LIKE :search OR u.firstname LIKE :search OR u.lastname LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Compter le total
        $countQuery = clone $queryBuilder;
        $total = count($countQuery->getQuery()->getResult());

        // Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('u.registrationAt', 'DESC');

        $users = $queryBuilder->getQuery()->getResult();

        return $this->json([
            'data' => $users,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ]
        ], Response::HTTP_OK, [], ['groups' => ['user:list']]);
    }

    #[Route('/users/{id}', name: 'api_admin_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showUser(int $id): JsonResponse
    {
        $user = $this->usersRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/orders', name: 'api_admin_orders', methods: ['GET'])]
    public function listOrders(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        $queryBuilder = $this->ordersRepository->createQueryBuilder('o')
            ->leftJoin('o.users', 'u')
            ->addSelect('u')
            ->leftJoin('o.vehicules', 'v')
            ->addSelect('v');

        if ($status) {
            $queryBuilder
                ->where('o.orderStatus = :status')
                ->setParameter('status', $status);
        }

        // Compter le total
        $countQuery = clone $queryBuilder;
        $total = count($countQuery->getQuery()->getResult());

        // Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('o.createdAt', 'DESC');

        $orders = $queryBuilder->getQuery()->getResult();

        return $this->json([
            'data' => $orders,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ]
        ], Response::HTTP_OK, [], ['groups' => ['order:list']]);
    }

    #[Route('/messages/conversations', name: 'api_admin_conversations', methods: ['GET'])]
    public function listConversations(): JsonResponse
    {
        // Récupérer toutes les conversations uniques
        $messages = $this->messagesRepository->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->leftJoin('m.recipient', 'r')
            ->addSelect('r')
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Grouper par conversationId
        $conversations = [];
        foreach ($messages as $message) {
            $conversationId = $message->getConversationId();
            if (!$conversationId) continue;

            if (!isset($conversations[$conversationId])) {
                $sender = $message->getSender();
                $recipient = $message->getRecipient();

                $conversations[$conversationId] = [
                    'conversationId' => $conversationId,
                    'participants' => [
                        $sender ? [
                            'id' => $sender->getId(),
                            'firstname' => $sender->getFirstname(),
                            'lastname' => $sender->getLastname(),
                            'email' => $sender->getEmail(),
                        ] : null,
                        $recipient ? [
                            'id' => $recipient->getId(),
                            'firstname' => $recipient->getFirstname(),
                            'lastname' => $recipient->getLastname(),
                            'email' => $recipient->getEmail(),
                        ] : null,
                    ],
                    'lastMessage' => [
                        'content' => $message->getContent(),
                        'sendAt' => $message->getSendAt()->format('c'),
                    ],
                    'messageCount' => 0,
                    'unreadCount' => 0,
                ];
            }

            $conversations[$conversationId]['messageCount']++;
            if (!$message->isRead()) {
                $conversations[$conversationId]['unreadCount']++;
            }
        }

        return $this->json(array_values($conversations));
    }

    #[Route('/stats', name: 'api_admin_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        // Statistiques générales
        $totalUsers = $this->usersRepository->count([]);
        $totalOrders = $this->ordersRepository->count([]);
        $pendingOrders = $this->ordersRepository->count(['orderStatus' => 'pending']);
        $paidOrders = $this->ordersRepository->count(['orderStatus' => 'paid']);

        // Revenus (somme des commandes payées)
        $revenueResult = $this->ordersRepository->createQueryBuilder('o')
            ->select('SUM(o.totalPrice) as totalRevenue')
            ->where('o.orderStatus = :status')
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getSingleScalarResult();

        $totalRevenue = $revenueResult ? (int) $revenueResult : 0;

        // Messages non lus
        $unreadMessages = $this->messagesRepository->count(['isRead' => false]);

        return $this->json([
            'users' => [
                'total' => $totalUsers,
            ],
            'orders' => [
                'total' => $totalOrders,
                'pending' => $pendingOrders,
                'paid' => $paidOrders,
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'formatted' => number_format($totalRevenue / 100, 2, ',', ' ') . ' €',
            ],
            'messages' => [
                'unread' => $unreadMessages,
            ],
        ]);
    }
}
