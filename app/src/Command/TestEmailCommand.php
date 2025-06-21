<?php

namespace App\Command;

use App\Service\EmailService;
use App\Entity\Orders;
use App\Entity\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test d\'envoi d\'email de confirmation d\'achat',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private EmailService $emailService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Récupérer une commande de test (n'importe laquelle)
            $order = $this->entityManager->getRepository(Orders::class)->findOneBy([]);
            
            if (!$order) {
                $io->error('Aucune commande trouvée pour le test.');
                return Command::FAILURE;
            }

            $user = $order->getUsers();
            if (!$user) {
                $io->error('Aucun utilisateur trouvé pour cette commande.');
                return Command::FAILURE;
            }

            $io->info('Envoi d\'email de test...');
            $io->info('Commande: #' . $order->getId());
            $io->info('Utilisateur: ' . $user->getEmail());
            $io->info('Statut: ' . $order->getOrderStatus());

            // Envoyer l'email de test
            $this->emailService->sendPurchaseConfirmation($order, $user);
            $this->emailService->sendAdminNotification($order, $user);

            $io->success('Emails envoyés avec succès !');
            $io->info('Vérifiez MailHog à l\'adresse: http://localhost:8025');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi d\'email: ' . $e->getMessage());
            $io->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
} 