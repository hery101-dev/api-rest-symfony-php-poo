<?php

namespace App\Repository;

use App\Entity\OfferView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OfferView>
 *
 * @method OfferView|null find($id, $lockMode = null, $lockVersion = null)
 * @method OfferView|null findOneBy(array $criteria, array $orderBy = null)
 * @method OfferView[]    findAll()
 * @method OfferView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OfferView::class);
    }

//    /**
//     * @return OfferView[] Returns an array of OfferView objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OfferView
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
