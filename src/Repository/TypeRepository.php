<?php

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Type>
 *
 * @method Type|null find($id, $lockMode = null, $lockVersion = null)
 * @method Type|null findOneBy(array $criteria, array $orderBy = null)
 * @method Type[]    findAll()
 * @method Type[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function findByFilters($filters): array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        foreach ($filters as $filterName => $filterValue) {
            switch ($filterName) {
                case 'search':
                    if ($filterValue) {
                        $queryBuilder = $this->findBySearch($queryBuilder, $filterValue);
                    }
                    break;
                case 'order':
                    if ($filterValue) {
                        $queryBuilder = $queryBuilder
                            ->orderBy('t.' . $filterValue, $filters['direction']);
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
            ->andWhere('t.label LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }


//    /**
//     * @return Type[] Returns an array of Type objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Type
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
