<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve le super administrateur existant
     */
    public function findSuperAdmin(): ?Users
    {
        $users = $this->findAll();
        
        foreach ($users as $user) {
            if ($user->isSuperAdmin()) {
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Vérifie s'il existe déjà un super administrateur
     */
    public function hasSuperAdmin(): bool
    {
        return $this->findSuperAdmin() !== null;
    }

    /**
     * Recherche des utilisateurs par nom, prénom ou email
     */
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.preferences', 'p')
            ->addSelect('p')
            ->andWhere('u.firstname LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les utilisateurs avec leurs préférences
     */
    public function findAllWithPreferences(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.preferences', 'p')
            ->addSelect('p')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Users[]
     */
    public function findInactiveUsers(\DateTimeInterface $limitDate): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.lastLoginAt < :date OR (u.lastLoginAt IS NULL AND u.registrationAt < :date)')
            ->setParameter('date', $limitDate)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Users[] Returns an array of Users objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
