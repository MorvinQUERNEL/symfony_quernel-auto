<?php

namespace App\Repository;

use App\Entity\Vehicules;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicules>
 */
class VehiculesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicules::class);
    }

    /**
     * Recherche des véhicules par marque, modèle, carburant, etc.
     */
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.pictures', 'p')
            ->addSelect('p')
            ->andWhere('v.brand LIKE :search OR v.model LIKE :search OR v.fuelType LIKE :search OR v.color LIKE :search OR v.trasmission LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('v.brand', 'ASC')
            ->addOrderBy('v.model', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les véhicules avec leurs images (évite les requêtes N+1)
     */
    public function findAllWithPictures(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.pictures', 'p')
            ->addSelect('p')
            ->orderBy('v.brand', 'ASC')
            ->addOrderBy('v.model', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les véhicules récents avec leurs images
     */
    public function findRecentWithPictures(int $limit = 6): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.pictures', 'p')
            ->addSelect('p')
            ->orderBy('v.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Vehicules[] Returns an array of Vehicules objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicules
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
