<?php

namespace App\MessageHandler;

use App\Message\DeleteInactiveUsersMessage;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteInactiveUsersMessageHandler
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(DeleteInactiveUsersMessage $message): void
    {
        $threeYearsAgo = new \DateTime('-3 years');
        $inactiveUsers = $this->usersRepository->findInactiveUsers($threeYearsAgo);

        if (empty($inactiveUsers)) {
            return;
        }

        foreach ($inactiveUsers as $user) {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();
    }
} 