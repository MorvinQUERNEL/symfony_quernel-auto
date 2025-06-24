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
    name: 'show:super_admin',
    description: 'Affiche les informations du super administrateur actuel',
)]
class ShowSuperAdminCommand extends Command
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
            $io->note('Vous pouvez en créer un avec la commande make:super_admin');
            return Command::FAILURE;
        }

        $io->success('Super administrateur trouvé :');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['ID', $existingSuperAdmin->getId()],
                ['Email', $existingSuperAdmin->getEmail()],
                ['Prénom', $existingSuperAdmin->getFirstname()],
                ['Nom', $existingSuperAdmin->getLastname()],
                ['Téléphone', $existingSuperAdmin->getPhoneNumber()],
                ['Adresse', $existingSuperAdmin->getAddress()],
                ['Ville', $existingSuperAdmin->getCity()],
                ['Code postal', $existingSuperAdmin->getPostalCode()],
                ['Pays', $existingSuperAdmin->getCountry()],
                ['Date d\'inscription', $existingSuperAdmin->getRegistrationAt()->format('d/m/Y H:i:s')],
                ['Rôles', implode(', ', $existingSuperAdmin->getRoles())],
            ]
        );

        return Command::SUCCESS;
    }
} 