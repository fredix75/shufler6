<?php

namespace App\Repository;

use App\Entity\Flux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flux>
 *
 * @method Flux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flux[]    findAll()
 * @method Flux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FluxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flux::class);
    }

    public function save(Flux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Flux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    function getNews(int $category): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 1)
            ->andWhere('a.mood= :category')
            ->setParameter('category', $category)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getPodcasts(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 2)
            ->leftJoin('a.channel', 'channel')
            ->addSelect('channel')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getRadios(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 3)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getLinks(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 4)
            ->orderBy('a.mood, a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getPlaylists(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 5)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Flux[] Returns an array of Flux objects
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

//    public function findOneBySomeField($value): ?Flux
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
