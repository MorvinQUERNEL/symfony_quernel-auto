<?php

namespace App\Command;

use App\Entity\Pictures;
use App\Entity\Vehicules;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-demo-vehicules',
    description: 'Seed the database with demo luxury vehicles for testing',
)]
class SeedDemoVehiculesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force seeding even if vehicles exist')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Clear existing demo vehicles before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Demo vehicles data
        $vehiclesData = [
            [
                'brand' => 'Ferrari',
                'model' => '458 Italia',
                'year' => new \DateTime('2022-01-01'),
                'mileage' => 12500,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Rouge Corsa',
                'doorCount' => 2,
                'description' => 'Superbe Ferrari 458 Italia en parfait état. Moteur V8 4.5L atmosphérique développant 570 chevaux. Intérieur cuir noir, carbone, système audio JBL. Historique complet Ferrari.',
                'status' => 'available',
                'salePrice' => 18900000, // 189 000 €
                'image' => 'ferrari-458.jpg',
            ],
            [
                'brand' => 'BMW',
                'model' => 'M3 Competition',
                'year' => new \DateTime('2023-01-01'),
                'mileage' => 8200,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Noir Saphir',
                'doorCount' => 4,
                'description' => 'BMW M3 Competition xDrive. Moteur 6 cylindres bi-turbo 510 ch. Pack Carbon, toit ouvrant, affichage tête haute, sièges M Sport. Garantie BMW jusqu\'en 2026.',
                'status' => 'available',
                'salePrice' => 9850000, // 98 500 €
                'image' => 'bmw-m3.jpg',
            ],
            [
                'brand' => 'Mercedes-Benz',
                'model' => 'Classe S 580',
                'year' => new \DateTime('2023-01-01'),
                'mileage' => 15000,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Noir Obsidienne',
                'doorCount' => 4,
                'description' => 'Mercedes Classe S 580 4MATIC. Le summum du luxe automobile. Intérieur cuir Nappa, pack Burmester 4D, conduite semi-autonome niveau 3, sièges massants.',
                'status' => 'available',
                'salePrice' => 14500000, // 145 000 €
                'image' => 'mercedes-sedan.jpg',
            ],
            [
                'brand' => 'Porsche',
                'model' => '911 Carrera S',
                'year' => new \DateTime('2022-01-01'),
                'mileage' => 18000,
                'fuelType' => 'Essence',
                'transmission' => 'PDK',
                'color' => 'Gris Agate',
                'doorCount' => 2,
                'description' => 'Porsche 911 992 Carrera S. Moteur flat-6 biturbo 450 ch. Pack Sport Chrono, échappement sport, jantes 20/21 pouces, PASM. Entretien Porsche exclusivement.',
                'status' => 'available',
                'salePrice' => 14200000, // 142 000 €
                'image' => 'porsche-911.jpg',
            ],
            [
                'brand' => 'Mercedes-AMG',
                'model' => 'GT 63 S',
                'year' => new \DateTime('2023-01-01'),
                'mileage' => 5500,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Gris Selenite',
                'doorCount' => 4,
                'description' => 'Mercedes-AMG GT 63 S 4MATIC+. Coupé 4 portes hautes performances. V8 biturbo 639 ch, 0-100 km/h en 3.2s. Pack AMG Carbon, freins céramique, suspension active.',
                'status' => 'available',
                'salePrice' => 17800000, // 178 000 €
                'image' => 'mercedes-amg.jpg',
            ],
            [
                'brand' => 'Lamborghini',
                'model' => 'Huracán EVO',
                'year' => new \DateTime('2021-01-01'),
                'mileage' => 9800,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Grigio Titans',
                'doorCount' => 2,
                'description' => 'Lamborghini Huracán EVO Coupé. V10 5.2L atmosphérique 640 ch. Design sensationnel, système LDVI, lifting system, intérieur Alcantara. Véhicule de collection.',
                'status' => 'available',
                'salePrice' => 24500000, // 245 000 €
                'image' => 'lamborghini.jpg',
            ],
            [
                'brand' => 'Porsche',
                'model' => '911 Turbo S',
                'year' => new \DateTime('2023-01-01'),
                'mileage' => 3200,
                'fuelType' => 'Essence',
                'transmission' => 'PDK',
                'color' => 'Blanc Carrara',
                'doorCount' => 2,
                'description' => 'Porsche 911 Turbo S Cabriolet. 650 ch, 0-100 en 2.7s. Configuration exclusive, pack Sport Design, jantes exclusives, intérieur cuir bicolore. État neuf.',
                'status' => 'available',
                'salePrice' => 26800000, // 268 000 €
                'image' => 'porsche-carrera.jpg',
            ],
            [
                'brand' => 'Aston Martin',
                'model' => 'DB11 V8',
                'year' => new \DateTime('2022-01-01'),
                'mileage' => 11000,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Bleu Mariana',
                'doorCount' => 2,
                'description' => 'Aston Martin DB11 V8 Coupé. Élégance britannique absolue. V8 biturbo AMG 510 ch, intérieur cuir et Alcantara, Bang & Olufsen, design iconique.',
                'status' => 'available',
                'salePrice' => 16900000, // 169 000 €
                'image' => 'aston-martin.jpg',
            ],
            [
                'brand' => 'Chevrolet',
                'model' => 'Corvette C8 Stingray',
                'year' => new \DateTime('2023-01-01'),
                'mileage' => 4500,
                'fuelType' => 'Essence',
                'transmission' => 'Automatique',
                'color' => 'Jaune Accelerate',
                'doorCount' => 2,
                'description' => 'Chevrolet Corvette C8 Stingray 3LT. Première Corvette à moteur central ! V8 6.2L 495 ch. Pack Z51, suspensions Magnetic Ride, intérieur complet.',
                'status' => 'available',
                'salePrice' => 11500000, // 115 000 €
                'image' => 'corvette.jpg',
            ],
            [
                'brand' => 'Ferrari',
                'model' => 'F8 Tributo',
                'year' => new \DateTime('2021-01-01'),
                'mileage' => 6800,
                'fuelType' => 'Essence',
                'transmission' => 'F1 DCT',
                'color' => 'Rosso Scuderia',
                'doorCount' => 2,
                'description' => 'Ferrari F8 Tributo, hommage au meilleur V8 de l\'histoire Ferrari. 720 ch, 0-100 en 2.9s. Configuration exclusive, carbone intérieur/extérieur, sièges racing.',
                'status' => 'available',
                'salePrice' => 29500000, // 295 000 €
                'image' => 'ferrari-red.jpg',
            ],
        ];

        // Check if demo vehicles already exist
        $existingCount = $this->entityManager->getRepository(Vehicules::class)
            ->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($existingCount > 0 && !$input->getOption('force') && !$input->getOption('clear')) {
            $io->warning(sprintf(
                '%d vehicles already exist in the database. Use --force to add demo vehicles anyway, or --clear to remove existing ones first.',
                $existingCount
            ));
            return Command::SUCCESS;
        }

        // Clear existing demo vehicles if requested
        if ($input->getOption('clear')) {
            $io->section('Clearing existing vehicles...');

            // Delete all pictures first
            $this->entityManager->createQuery('DELETE FROM App\Entity\Pictures')->execute();
            // Delete all vehicles
            $this->entityManager->createQuery('DELETE FROM App\Entity\Vehicules')->execute();

            $io->success('Existing vehicles cleared.');
        }

        $io->section('Seeding demo vehicles...');

        $createdCount = 0;
        foreach ($vehiclesData as $data) {
            $vehicule = new Vehicules();
            $vehicule->setBrand($data['brand']);
            $vehicule->setModel($data['model']);
            $vehicule->setYear($data['year']);
            $vehicule->setMileage($data['mileage']);
            $vehicule->setFuelType($data['fuelType']);
            $vehicule->setTrasmission($data['transmission']);
            $vehicule->setColor($data['color']);
            $vehicule->setDoorCount($data['doorCount']);
            $vehicule->setDescription($data['description']);
            $vehicule->setStatus($data['status']);
            $vehicule->setSalePrice($data['salePrice']);

            // Create picture
            $picture = new Pictures();
            $picture->setName('/uploads/vehicules/demo/' . $data['image']);
            $picture->setDescription($data['brand'] . ' ' . $data['model']);
            $vehicule->addPicture($picture);

            $this->entityManager->persist($vehicule);
            $createdCount++;

            $io->writeln(sprintf(
                '  ✓ %s %s - %s € - %s',
                $data['brand'],
                $data['model'],
                number_format($data['salePrice'] / 100, 0, ',', ' '),
                $data['status']
            ));
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d demo vehicles have been created successfully!', $createdCount));

        $io->note([
            'All vehicles are set to "available" status for payment testing.',
            'Images are stored in: public/uploads/vehicules/demo/',
            'To clear and reseed: php bin/console app:seed-demo-vehicules --clear',
        ]);

        return Command::SUCCESS;
    }
}
