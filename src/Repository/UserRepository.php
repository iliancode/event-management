<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByFilters($filters): array
    {
        $queryBuilder = $this->createQueryBuilder('u');

        foreach ($filters as $filterName => $filterValue) {
            switch ($filterName) {
                case 'search':
                    if ($filterValue) {
                        $queryBuilder = $this->findBySearch($queryBuilder, $filterValue);
                    }
                    break;
                case 'roles':
                    if ($filterValue) {
                        $queryBuilder = $this->findByRoles($queryBuilder, $filterValue);
                    }
                    break;
                case 'verified':
                    if ($filterValue) {
                        $queryBuilder = $this->findByVerified($queryBuilder, $filterValue);
                    }
                    break;
                case 'banned':
                    if ($filterValue) {
                        $queryBuilder = $this->findByBanned($queryBuilder, $filterValue);
                    }
                    break;
                case 'order':
                    if ($filterValue) {
                        $queryBuilder = $queryBuilder
                            ->orderBy('u.' . $filterValue, $filters['direction']);
                    }
                    break;
            }
        }
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }


    private function findBySearch(QueryBuilder $queryBuilder, String $search): QueryBuilder
    {
        return $queryBuilder
                ->andWhere('u.username LIKE :search')
                ->setParameter('search', '%' . $search . '%');
    }

    private function findByRoles(QueryBuilder $queryBuilder, array $roles): QueryBuilder
    {
        $orX = $queryBuilder->expr()->orX();
        foreach ($roles as $role) {
            $orX->add($queryBuilder->expr()->like('u.roles', ':role_' . $role));
            $queryBuilder->setParameter('role_' . $role, '%"'.$role.'"%');
        }
        return $queryBuilder
            ->andWhere($orX);
    }

    private function findByVerified(QueryBuilder $queryBuilder, array $verifieds): QueryBuilder
    {
        if (count($verifieds) === 2) {
            return $queryBuilder;
        }
        return $queryBuilder
            ->andWhere('u.verified = :verified')
            ->setParameter('verified', $verifieds[0]);
    }

    private function findByBanned(QueryBuilder $queryBuilder, array $banneds): QueryBuilder
    {
        if (count($banneds) === 2) {
            return $queryBuilder;
        }
        return $queryBuilder
            ->andWhere('u.banned = :banned')
            ->setParameter('banned', $banneds[0]);
    }

//    /**
//     * @return User[] Returns an array of User objects
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

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
