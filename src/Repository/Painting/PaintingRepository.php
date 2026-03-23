<?php

namespace App\Repository\Painting;

use App\Entity\PictureCollection\Painting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Painting>
 */
class PaintingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Painting::class);
    }

    public function getRandomPaintings() {
        $conn = $this->getEntityManager()->getConnection();
        $query = <<<SQL
SELECT distinct painter.id, pa.file, painter.*  FROM painting pa LEFT JOIN painter ON painter.id = pa.painter_id ORDER BY RAND() LIMIT 20
SQL;
        $stmt = $conn->prepare($query);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    //    /**
    //     * @return Painting[] Returns an array of Painting objects
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

    //    public function findOneBySomeField($value): ?Painting
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
