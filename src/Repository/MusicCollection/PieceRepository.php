<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\Piece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PieceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Piece::class);
    }

    public function getPieces(array $params = []): array
    {
        $sql= "SELECT t.*, t.youtube_key as youtubeKey, COALESCE(t.extra_note, t.note) as note, a.picture FROM piece t LEFT JOIN album a ON a.name = t.album AND a.auteur = t.artiste WHERE TRUE ";
        $orderBy = [];
        $p = [];

        if (!empty($params['hasYoutubeKey'])) {
            $sql .= "AND t.youtube_key <> '' ";
        }

        if (!empty($params['auteur'])) {
            $sql .= "AND (t.auteur LIKE ? OR t.artiste LIKE ?) ";
            $p[] = '%'.$params['auteur'].'%';
            $p[] = '%'.$params['auteur'].'%';
        }
        if (!empty($params['album'])) {
            $sql .= "AND t.album LIKE ? ";
            $p[] = '%'.$params['album'].'%';
            $orderBy[] = "t.numero ASC ";
        } else {
            $orderBy[] = "t.titre ASC ";
        }
        if (!empty($params['genres'])) {
            $sql .= "AND t.genre IN (" . str_repeat('?,', count($params['genres']) - 1) . "?) ";
            $p = array_merge($p, $params['genres']);
        }
        if (!empty($params['note'])) {
            $sql .= "AND t.extra_note >= ? ";
            $p[] = $params['note'];
        }
        if (!empty($params['annee'])) {
            $annee = $params['annee'];
            if (substr_count($annee, '-')) {
                $annee1 = (explode('-', $annee)[0] && is_numeric(explode('-', $annee)[0])) ? explode('-', $annee)[0] : 1;
                $annee2 = (explode('-', $annee)[1] && is_numeric(explode('-', $annee)[1])) ? explode('-', $annee)[1] : date('Y');
                if ($annee1 && $annee2) {
                    $sql .= "AND t.annee >= ? AND t.annee <= ? ";
                    $p[] = $annee1;
                    $p[] = $annee2;
                }
            } elseif (is_numeric($annee)) {
                $sql .= "AND t.annee = ? ";
                $p[] = $params['annee'];
            }
        }

        if (!empty($params['search'])) {
            $sql .= "AND (t.auteur LIKE ? OR t.titre LIKE ? OR t.album LIKE ? OR t.artiste LIKE ?) ";
            $p[] = '%'.$params['search'].'%';
            $p[] = '%'.$params['search'].'%';
            $p[] = '%'.$params['search'].'%';
            $p[] = '%'.$params['search'].'%';
        }

        $rawSQL = $sql;
        $rawSQL .= $orderBy ? 'ORDER BY ' . implode(', ', $orderBy) : '';
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($rawSQL);
        foreach($p as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function getGenres(): array
    {
        return $this->createQueryBuilder('p')->select('distinct p.genre')
            ->orderBy('p.genre')
            ->getQuery()->getResult();
    }


    //    /**
    //     * @return Piece[] Returns an array of Piece objects
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

    //    public function findOneBySomeField($value): ?Piece
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
