<?php

namespace App\Repository;

use App\Entity\FluxType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FluxType>
 *
 * @method FluxType|null find($id, $lockMode = null, $lockVersion = null)
 * @method FluxType|null findOneBy(array $criteria, array $orderBy = null)
 * @method FluxType[]    findAll()
 * @method FluxType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FluxTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FluxType::class);
    }

//    /**
//     * @return FluxType[] Returns an array of FluxType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FluxType
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
