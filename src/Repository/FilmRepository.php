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

    public function getFilmFull(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
SELECT
    f.*,
    (
        SELECT JSON_ARRAYAGG(t.name)
        FROM (
            SELECT DISTINCT g.name
            FROM JSON_TABLE(f.genres, '$[*]'
                COLUMNS (genres_id INT PATH '$')
            ) jt
            JOIN genrefilm g ON g.tmdb_id = jt.genres_id
        ) t
    ) AS genres2,
    (
        SELECT JSON_ARRAYAGG(pf.path)
        FROM picture_film pf
        WHERE pf.film_id = f.id
    ) AS pictures
FROM film f
WHERE f.id = :id
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('id', $id);
        return $stmt->executeQuery()->fetchAssociative();
    }

    public function findByDateIntervall(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.date > :start')
            ->andWhere('f.date <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('f.popularity', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
