<?php

namespace App\Repository;

use App\Entity\Flux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flux>
 *
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

    public function find(mixed $id, $lockMode = null, $lockVersion = null): ?Flux
    {
        $q = $this->createQueryBuilder('a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('a.channel', 'channel')
            ->addSelect('channel');

        return $q->getQuery()->getOneOrNullResult();
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
            ->leftJoin('a.mood', 'mood')
            ->addSelect('mood')
            ->leftJoin('a.type', 'type')
            ->addSelect('type')
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
            ->leftJoin('a.type', 'type')
            ->addSelect('type')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getRadios(): array
    {
        $q = $this->createQueryBuilder('f')
            ->select('f')
            ->where('f.type= :type')
            ->setParameter('type', 3)
            ->join('f.type', 'type')
            ->join('f.mood', 'mood')
            ->addSelect('type')
            ->addSelect('mood')
            ->orderBy('f.id', 'ASC')
            ->getQuery();

            return $q->getResult();
    }

    function getLinks(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type= :type')
            ->setParameter('type', 4)
            ->leftJoin('a.mood', 'mood')
            ->addSelect('mood')
            ->leftJoin('a.type', 'type')
            ->addSelect('type')
            ->orderBy('a.mood, a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    function getPlaylists(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.type', 'type')
            ->addSelect('type')
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
