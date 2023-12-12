<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByFilters($filters): array
    {
        $queryBuilder = $this->createQueryBuilder('e');

        foreach ($filters as $filterName => $filterValue) {
            switch ($filterName) {
                case 'search':
                    if ($filterValue) {
                        $queryBuilder = $this->findBySearch($queryBuilder, $filterValue);
                    }
                    break;
                case 'types':
                    if ($filterValue && count($filterValue) > 0) {
                        $queryBuilder = $this->findByTypes($queryBuilder, $filterValue);
                    }
                    break;
                case 'dateStart':
                    if ($filterValue) {
                        $queryBuilder = $this->findByDateStart($queryBuilder, $filterValue);
                    }
                    break;
                case 'dateEnd':
                    if ($filterValue) {
                        $queryBuilder = $this->findByDateEnd($queryBuilder, $filterValue);
                    }
                    break;
                case 'state':
                    if ($filterValue && count($filterValue) > 0) {
                        $queryBuilder = $this->findByState($queryBuilder, $filterValue);
                    }
                    break;
                case 'order':
                    if ($filterValue) {
                        $queryBuilder = $queryBuilder
                            ->orderBy('e.' . $filterValue, $filters['direction']);
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
            ->andWhere('e.title LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    private function findByTypes(QueryBuilder $queryBuilder, $types): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('e.type IN (:types)')
            ->setParameter('types', $types);
    }

    private function findByDateStart(QueryBuilder $queryBuilder, $dateStart): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('e.date >= :dateStart')
            ->setParameter('dateStart', $dateStart);
    }

    private function findByDateEnd(QueryBuilder $queryBuilder, $dateEnd): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('e.date <= :dateEnd')
            ->setParameter('dateEnd', $dateEnd);
    }

    private function findByState(QueryBuilder $queryBuilder, $state): QueryBuilder
    {
        if (count($state) === 2) {
            return $queryBuilder;
        }

        if ($state[0] === 'coming') {
            return $queryBuilder->andWhere('e.date >= :now')
                ->setParameter('now', new \DateTime());
        } else {
            return $queryBuilder->andWhere('e.date < :now')
                ->setParameter('now', new \DateTime());
        }
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
