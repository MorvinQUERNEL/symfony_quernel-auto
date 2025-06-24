<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Stripe\StripeClient;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'stripe:webhook:setup',
    description: 'Configure les webhooks Stripe pour l\'application',
)]
class StripeWebhookCommand extends Command
{
    private StripeClient $stripeClient;
    private LoggerInterface $logger;

    public function __construct(StripeClient $stripeClient, LoggerInterface $logger)
    {
        parent::__construct();
        $this->stripeClient = $stripeClient;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Configuration des Webhooks Stripe');

        try {
            // Récupérer la liste des webhooks existants
            $webhooks = $this->stripeClient->webhookEndpoints->all(['limit' => 100]);
            
            $io->section('Webhooks existants :');
            
            if (empty($webhooks->data)) {
                $io->text('Aucun webhook configuré.');
            } else {
                foreach ($webhooks->data as $webhook) {
                    $io->text(sprintf(
                        '• %s - %s (Statut: %s)',
                        $webhook->id,
                        $webhook->url,
                        $webhook->status
                    ));
                }
            }

            $io->section('Configuration recommandée :');
            $io->text('URL du webhook : https://votre-domaine.com/webhook/stripe');
            $io->text('Événements à écouter :');
            $io->listing([
                'checkout.session.completed',
                'checkout.session.expired',
                'payment_intent.succeeded',
                'payment_intent.payment_failed',
                'payment_intent.canceled',
                'invoice.payment_succeeded',
                'invoice.payment_failed'
            ]);

            $io->section('Instructions :');
            $io->text('1. Connectez-vous à votre dashboard Stripe');
            $io->text('2. Allez dans Développeurs > Webhooks');
            $io->text('3. Cliquez sur "Ajouter un endpoint"');
            $io->text('4. Entrez l\'URL de votre webhook');
            $io->text('5. Sélectionnez les événements listés ci-dessus');
            $io->text('6. Copiez la clé secrète du webhook');
            $io->text('7. Mettez à jour le paramètre stripe_webhook_secret dans config/services.yaml');

            $io->section('Test du webhook :');
            if ($io->confirm('Voulez-vous tester un webhook existant ?')) {
                $this->testWebhook($io);
            }

            $io->success('Configuration des webhooks terminée !');

        } catch (\Exception $e) {
            $io->error('Erreur lors de la configuration : ' . $e->getMessage());
            $this->logger->error('Erreur dans la commande webhook Stripe', [
                'error' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function testWebhook(SymfonyStyle $io): void
    {
        $webhooks = $this->stripeClient->webhookEndpoints->all(['limit' => 10]);
        
        if (empty($webhooks->data)) {
            $io->warning('Aucun webhook à tester.');
            return;
        }

        $webhookChoices = [];
        foreach ($webhooks->data as $webhook) {
            $webhookChoices[$webhook->id] = sprintf('%s - %s', $webhook->id, $webhook->url);
        }

        $selectedWebhookId = $io->choice('Sélectionnez un webhook à tester :', $webhookChoices);

        try {
            $testEvent = $this->stripeClient->webhookEndpoints->createTestEvent($selectedWebhookId);
            
            $io->success('Événement de test envoyé avec succès !');
            $io->text('ID de l\'événement : ' . $testEvent->id);
            $io->text('Type d\'événement : ' . $testEvent->type);
            
        } catch (\Exception $e) {
            $io->error('Erreur lors du test : ' . $e->getMessage());
        }
    }
} 