<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'remove:super_admin',
    description: 'Supprime le super administrateur existant',
)]
class RemoveSuperAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si un super admin existe
        $existingSuperAdmin = $this->entityManager->getRepository(Users::class)->findSuperAdmin();

        if (!$existingSuperAdmin) {
            $io->error('Aucun super administrateur trouvé dans le système.');
            return Command::FAILURE;
        }

        $io->warning('Vous êtes sur le point de supprimer le super administrateur suivant :');
        $io->note('Email: ' . $existingSuperAdmin->getEmail());
        $io->note('Prénom: ' . $existingSuperAdmin->getFirstname());
        $io->note('Nom: ' . $existingSuperAdmin->getLastname());

        if (!$io->confirm('Êtes-vous sûr de vouloir supprimer ce super administrateur ?', false)) {
            $io->error('Opération annulée.');
            return Command::FAILURE;
        }

        // Supprimer le super admin
        $this->entityManager->remove($existingSuperAdmin);
        $this->entityManager->flush();

        $io->success('Super administrateur supprimé avec succès !');
        $io->note('Vous pouvez maintenant créer un nouveau super administrateur avec la commande make:super_admin');

        return Command::SUCCESS;
    }
} 