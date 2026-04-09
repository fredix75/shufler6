<?php

namespace App\Repository;

use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    public function getFilm(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
SELECT f.*, JSON_ARRAYAGG(g.name) AS genres2
FROM film f
         JOIN JSON_TABLE(f.genres, '$[*]'
                         COLUMNS (genres_id INT PATH '$')
    ) jt
         JOIN genrefilm g ON g.tmdb_id = jt.genres_id
WHERE f.verified = 0
GROUP BY f.id
ORDER BY f.id
LIMIT 1
SQL;
        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery()->fetchAssociative();
    }

    //    /**
    //     * @return Film[] Returns an array of Film objects
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

    //    public function findOneBySomeField($value): ?Film
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
