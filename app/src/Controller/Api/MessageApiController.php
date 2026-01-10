<?php

namespace App\Controller\Api;

use App\Entity\Messages;
use App\Entity\Users;
use App\Repository\MessagesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/messages')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class MessageApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessagesRepository $messagesRepository,
        private UsersRepository $usersRepository,
    ) {
    }

    #[Route('', name: 'api_messages_conversations', methods: ['GET'])]
    public function getConversations(): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        // Récupérer les conversations de l'utilisateur
        // Le conversationId format: user_{userId}
        $conversationId = 'user_' . $user->getId();

        $messages = $this->messagesRepository->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->where('m.conversationId = :convId')
            ->setParameter('convId', $conversationId)
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Grouper par conversationId
        $conversations = [];
        foreach ($messages as $message) {
            $convId = $message->getConversationId();
            if (!$convId) continue;

            if (!isset($conversations[$convId])) {
                $conversations[$convId] = [
                    'conversationId' => $convId,
                    'lastMessage' => [
                        'content' => $message->getContent(),
                        'sendAt' => $message->getSendAt()->format('c'),
                        'isFromUser' => $message->isFromUser(),
                    ],
                    'unreadCount' => 0,
                    'messageCount' => 0,
                ];
            }

            $conversations[$convId]['messageCount']++;

            // Compter les messages non lus (messages reçus par l'utilisateur, donc pas isFromUser)
            if (!$message->isRead() && !$message->isFromUser()) {
                $conversations[$convId]['unreadCount']++;
            }
        }

        return $this->json(array_values($conversations));
    }

    #[Route('/{conversationId}', name: 'api_messages_conversation', methods: ['GET'])]
    public function getConversation(string $conversationId): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        // Vérifier que l'utilisateur a accès à cette conversation
        $expectedConvId = 'user_' . $user->getId();
        if ($conversationId !== $expectedConvId && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Récupérer les messages de la conversation
        $messages = $this->messagesRepository->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->where('m.conversationId = :conversationId')
            ->setParameter('conversationId', $conversationId)
            ->orderBy('m.sendAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Marquer les messages reçus comme lus (messages non isFromUser pour l'utilisateur)
        foreach ($messages as $message) {
            // Si l'utilisateur est l'auteur de la conversation et le message vient d'un admin
            if (!$message->isFromUser() && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $this->entityManager->flush();

        // Formater les messages
        $formattedMessages = array_map(function ($message) {
            $sender = $message->getSender();
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sendAt' => $message->getSendAt()->format('c'),
                'isFromUser' => $message->isFromUser(),
                'isRead' => $message->isRead(),
                'sender' => $sender ? [
                    'id' => $sender->getId(),
                    'firstname' => $sender->getFirstname(),
                    'lastname' => $sender->getLastname(),
                ] : null,
            ];
        }, $messages);

        return $this->json([
            'conversationId' => $conversationId,
            'messages' => $formattedMessages,
        ]);
    }

    #[Route('', name: 'api_messages_send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['content'])) {
            return $this->json(['error' => 'Le contenu du message est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Déterminer le conversationId
        $conversationId = $data['conversationId'] ?? null;

        // Si pas de conversationId, créer un basé sur l'utilisateur
        if (!$conversationId) {
            $conversationId = 'user_' . $user->getId();
        }

        $message = new Messages();
        $message->setContent($data['content']);
        $message->setSender($user);
        $message->setSendAt(new \DateTimeImmutable());
        $message->setIsRead(false);
        $message->setConversationId($conversationId);

        // isFromUser = true si l'utilisateur n'est pas admin
        $message->setIsFromUser(!$this->isGranted('ROLE_ADMIN'));

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sendAt' => $message->getSendAt()->format('c'),
            'conversationId' => $conversationId,
            'isFromUser' => $message->isFromUser(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/unread-count', name: 'api_messages_unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        // Pour un admin, compter tous les messages non lus des utilisateurs
        if ($this->isGranted('ROLE_ADMIN')) {
            $count = $this->messagesRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.isRead = false')
                ->andWhere('m.isFromUser = true')
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            // Messages non lus dans la conversation de l'utilisateur (réponses d'admin)
            $count = $this->messagesRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.isRead = false')
                ->andWhere('m.conversationId = :convId')
                ->andWhere('m.isFromUser = false')
                ->setParameter('convId', 'user_' . $user->getId())
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->json(['unreadCount' => (int) $count]);
    }
}
