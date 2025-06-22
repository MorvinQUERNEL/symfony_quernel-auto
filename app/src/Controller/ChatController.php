<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\ChatMessageType;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chat')]
class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(MessagesRepository $messagesRepository): Response
    {
        $user = $this->getUser();
        $conversationId = $user->getId() . '_' . date('Y-m-d');
        
        // Récupérer tous les messages de cette conversation
        $messages = $messagesRepository->findBy(
            ['conversationId' => $conversationId],
            ['sendAt' => 'ASC']
        );
        
        // Marquer les messages comme lus si l'utilisateur est admin
        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($messages as $message) {
                if (!$message->isRead()) {
                    $message->setIsRead(true);
                }
            }
            $messagesRepository->getEntityManager()->flush();
        }
        
        $form = $this->createForm(ChatMessageType::class);
        
        return $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'conversationId' => $conversationId,
            'form' => $form,
        ]);
    }

    #[Route('/send', name: 'app_chat_send', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function send(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Le message ne peut pas être vide'], 400);
        }
        
        $message = new Messages();
        $message->setContent(trim($data['content']));
        $message->setSendAt(new \DateTimeImmutable());
        $message->setSender($user);
        $message->setConversationId($data['conversationId'] ?? $user->getId() . '_' . date('Y-m-d'));
        $message->setIsFromUser(!$this->isGranted('ROLE_ADMIN'));
        $message->setIsRead(false);
        
        $entityManager->persist($message);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sendAt' => $message->getSendAt()->format('H:i'),
                'isFromUser' => $message->isFromUser(),
                'senderName' => $user->getFirstname() . ' ' . $user->getLastname(),
            ]
        ]);
    }

    #[Route('/admin', name: 'app_chat_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(MessagesRepository $messagesRepository): Response
    {
        // Récupérer toutes les conversations uniques
        $conversations = $messagesRepository->createQueryBuilder('m')
            ->select('DISTINCT m.conversationId')
            ->orderBy('m.conversationId', 'DESC')
            ->getQuery()
            ->getResult();
        
        $conversationsData = [];
        foreach ($conversations as $conversation) {
            $conversationId = $conversation['conversationId'];
            
            // Récupérer le dernier message de cette conversation
            $lastMessage = $messagesRepository->createQueryBuilder('m')
                ->where('m.conversationId = :conversationId')
                ->setParameter('conversationId', $conversationId)
                ->orderBy('m.sendAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            // Compter les messages non lus
            $unreadCount = $messagesRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.conversationId = :conversationId')
                ->andWhere('m.isRead = :isRead')
                ->andWhere('m.isFromUser = :isFromUser')
                ->setParameter('conversationId', $conversationId)
                ->setParameter('isRead', false)
                ->setParameter('isFromUser', true)
                ->getQuery()
                ->getSingleScalarResult();
            
            $conversationsData[] = [
                'conversationId' => $conversationId,
                'lastMessage' => $lastMessage,
                'unreadCount' => $unreadCount,
            ];
        }
        
        return $this->render('chat/admin.html.twig', [
            'conversations' => $conversationsData,
        ]);
    }

    #[Route('/admin/conversation/{conversationId}', name: 'app_chat_admin_conversation', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminConversation(string $conversationId, MessagesRepository $messagesRepository): Response
    {
        // Récupérer tous les messages de cette conversation
        $messages = $messagesRepository->findBy(
            ['conversationId' => $conversationId],
            ['sendAt' => 'ASC']
        );
        
        // Marquer les messages comme lus
        foreach ($messages as $message) {
            if (!$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $messagesRepository->getEntityManager()->flush();
        
        $form = $this->createForm(ChatMessageType::class);
        
        return $this->render('chat/admin_conversation.html.twig', [
            'messages' => $messages,
            'conversationId' => $conversationId,
            'form' => $form,
        ]);
    }

    #[Route('/messages/{conversationId}', name: 'app_chat_get_messages', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getMessages(string $conversationId, MessagesRepository $messagesRepository): JsonResponse
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur a accès à cette conversation
        if (!$this->isGranted('ROLE_ADMIN') && !str_starts_with($conversationId, $user->getId() . '_')) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }
        
        $messages = $messagesRepository->findBy(
            ['conversationId' => $conversationId],
            ['sendAt' => 'ASC']
        );
        
        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sendAt' => $message->getSendAt()->format('H:i'),
                'isFromUser' => $message->isFromUser(),
                'senderName' => $message->getSender() ? $message->getSender()->getFirstname() . ' ' . $message->getSender()->getLastname() : 'Anonyme',
                'isRead' => $message->isRead(),
            ];
        }
        
        return new JsonResponse($messagesData);
    }

    #[Route('/unread-count', name: 'app_chat_unread_count', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getUnreadCount(MessagesRepository $messagesRepository): JsonResponse
    {
        $unreadCount = $messagesRepository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.isRead = :isRead')
            ->andWhere('m.isFromUser = :isFromUser')
            ->setParameter('isRead', false)
            ->setParameter('isFromUser', true)
            ->getQuery()
            ->getSingleScalarResult();
        
        return new JsonResponse(['unreadCount' => $unreadCount]);
    }
} 