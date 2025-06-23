<?php

namespace App\Command;

use App\Repository\OrdersRepository;
use App\Service\VehiculeStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-expired-orders',
    description: 'Nettoie les commandes expirées et libère les véhicules associés',
)]
class CleanExpiredOrdersCommand extends Command
{
    private OrdersRepository $ordersRepository;
    private EntityManagerInterface $entityManager;
    private VehiculeStatusService $vehiculeStatusService;

    public function __construct(
        OrdersRepository $ordersRepository,
        EntityManagerInterface $entityManager,
        VehiculeStatusService $vehiculeStatusService
    ) {
        parent::__construct();
        $this->ordersRepository = $ordersRepository;
        $this->entityManager = $entityManager;
        $this->vehiculeStatusService = $vehiculeStatusService;
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Exécuter en mode test sans modifications')
            ->addOption('hours', null, InputOption::VALUE_REQUIRED, 'Nombre d\'heures avant expiration', 24)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        
        $io->title('Nettoyage des commandes expirées');

        // Récupérer les commandes expirées
        $expiredOrders = $this->ordersRepository->findExpiredOrders();
        
        if (empty($expiredOrders)) {
            $io->success('Aucune commande expirée trouvée.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('%d commande(s) expirée(s) trouvée(s)', count($expiredOrders)));

        foreach ($expiredOrders as $order) {
            $io->text(sprintf(
                'Commande #%d - Créée le %s - Montant: %s €',
                $order->getId(),
                $order->getCreatedAt()->format('d/m/Y H:i'),
                number_format($order->getTotalPrice() / 100, 2, ',', ' ')
            ));

            if (!$dryRun) {
                // Marquer la commande comme expirée
                $order->setOrderStatus('expired');
                
                // Libérer les véhicules associés
                foreach ($order->getVehicules() as $vehicule) {
                    $vehicule->setStatus('Disponible');
                    $vehicule->setOrders(null);
                    $io->text(sprintf('  → Véhicule %s %s libéré', $vehicule->getBrand(), $vehicule->getModel()));
                }
                
                $this->entityManager->persist($order);
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $io->success(sprintf('%d commande(s) marquée(s) comme expirée(s) et véhicules libérés.', count($expiredOrders)));
        } else {
            $io->warning('Mode test activé - Aucune modification effectuée');
        }

        return Command::SUCCESS;
    }
} 