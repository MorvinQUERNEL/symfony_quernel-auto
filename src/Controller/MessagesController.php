<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\MessagesType;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
class MessagesController extends AbstractController
{
    #[Route('/', name: 'app_messages_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(MessagesRepository $messagesRepository): Response
    {
        return $this->render('messages/index.html.twig', [
            'messages' => $messagesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_messages_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Messages();
        $message->setSendAt(new \DateTimeImmutable());
        $message->setSender($this->getUser());

        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Le message a été envoyé avec succès !');
            return $this->redirectToRoute('app_messages_my_messages');
        }

        return $this->render('messages/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_messages_show', methods: ['GET'])]
    public function show(Messages $message): Response
    {
        // Vérifier que l'utilisateur est l'expéditeur ou le destinataire
        $user = $this->getUser();
        if ($message->getSender() !== $user && $message->getRecipient() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce message.');
        }

        return $this->render('messages/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_messages_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Messages $message, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est l'expéditeur ou admin
        $user = $this->getUser();
        if ($message->getSender() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce message.');
        }

        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le message a été modifié avec succès !');
            return $this->redirectToRoute('app_messages_show', ['id' => $message->getId()]);
        }

        return $this->render('messages/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_messages_delete', methods: ['POST'])]
    public function delete(Request $request, Messages $message, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est l'expéditeur ou admin
        $user = $this->getUser();
        if ($message->getSender() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce message.');
        }

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();

            $this->addFlash('success', 'Le message a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_messages_my_messages');
    }

    #[Route('/my-messages', name: 'app_messages_my_messages', methods: ['GET'])]
    public function myMessages(MessagesRepository $messagesRepository): Response
    {
        $user = $this->getUser();
        
        return $this->render('messages/my_messages.html.twig', [
            'sent_messages' => $messagesRepository->findBy(['sender' => $user], ['sendAt' => 'DESC']),
            'received_messages' => $messagesRepository->findBy(['recipient' => $user], ['sendAt' => 'DESC']),
        ]);
    }
} 