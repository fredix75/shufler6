<?php

namespace App\Repository\Painting;

use App\Entity\PictureCollection\Painter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Painter>
 */
class PainterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Painter::class);
    }

    public function getPaintersAndPaintings(int $nb, int $offset = 0): Paginator
    {
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.paintings', 'paintings')
            ->addSelect('paintings')
            ->where('p.type IS NULL')
            ->orderBy('p.birthYear', 'ASC')
            ->addOrderBy('p.deathYear', 'ASC')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($nb);

        return new Paginator($q, true);
    }

    public function getPainterAndPaintings(int $id): Painter
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.paintings', 'paintings')
            ->addSelect('paintings')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Painter[] Returns an array of Painter objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Painter
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
