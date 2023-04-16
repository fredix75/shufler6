<?php

namespace App\Repository;

use App\Entity\ChannelFlux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChannelFlux>
 *
 * @method ChannelFlux|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChannelFlux|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChannelFlux[]    findAll()
 * @method ChannelFlux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChannelFluxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelFlux::class);
    }

    function getChannelFluxAudio(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->where('c.providerName is NULL');
    }

    function getChannelFluxVideo(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.providerName IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function save(ChannelFlux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ChannelFlux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ChannelFlux[] Returns an array of ChannelFlux objects
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

//    public function findOneBySomeField($value): ?ChannelFlux
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
