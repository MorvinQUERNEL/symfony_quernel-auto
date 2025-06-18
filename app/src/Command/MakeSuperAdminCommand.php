<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'make:super_admin',
    description: 'Crée un super administrateur avec tous les rôles',
)]
class MakeSuperAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email du super admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe du super admin')
            ->addArgument('firstname', InputArgument::REQUIRED, 'Prénom du super admin')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Nom du super admin')
            // ->addArgument('phone', InputArgument::REQUIRED, 'Numéro de téléphone')
            // ->addArgument('address', InputArgument::REQUIRED, 'Adresse')
            // ->addArgument('city', InputArgument::REQUIRED, 'Ville')
            // ->addArgument('postal_code', InputArgument::REQUIRED, 'Code postal')
            // ->addArgument('country', InputArgument::REQUIRED, 'Pays')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        // $phone = $input->getArgument('phone');
        // $address = $input->getArgument('address');
        // $city = $input->getArgument('city');
        // $postalCode = $input->getArgument('postal_code');
        // $country = $input->getArgument('country');

        // Vérifier si un super admin existe déjà
        $existingSuperAdmin = $this->entityManager->getRepository(Users::class)->findSuperAdmin();

        if ($existingSuperAdmin) {
            $io->error('Un super administrateur existe déjà dans le système. Il ne peut y avoir qu\'un seul super admin.');
            $io->note('Super admin existant: ' . $existingSuperAdmin->getEmail());
            return Command::FAILURE;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        // Créer le super admin
        $user = new Users();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPhoneNumber("0000000000");
        $user->setAddress("0000000000");
        $user->setCity("0000000000");
        $user->setPostalCode((int)"00000");
        $user->setCountry("0000000000");
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setRegistrationAt(new \DateTimeImmutable());

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarder en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Super admin créé avec succès ! Email: %s, Prénom: %s, Nom: %s',
            $email,
            $firstname,
            $lastname
        ));

        return Command::SUCCESS;
    }
}
