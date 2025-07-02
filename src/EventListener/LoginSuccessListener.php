<?php

namespace App\EventListener;

use App\Entity\Users;
use App\Message\DeleteInactiveUsersMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
final class LoginSuccessListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof Users) {
            return;
        }

        $user->setLastLoginAt(new \DateTime());
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteInactiveUsersMessage());
    }
} 