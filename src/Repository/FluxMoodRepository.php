<?php

namespace App\Repository;

use App\Entity\FluxMood;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FluxMood>
 *
 * @method FluxMood|null find($id, $lockMode = null, $lockVersion = null)
 * @method FluxMood|null findOneBy(array $criteria, array $orderBy = null)
 * @method FluxMood[]    findAll()
 * @method FluxMood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FluxMoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FluxMood::class);
    }

//    /**
//     * @return FluxMood[] Returns an array of FluxMood objects
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

//    public function findOneBySomeField($value): ?FluxMood
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
