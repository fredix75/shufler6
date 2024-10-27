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
        $orderBy = "";
        $p = [];

        if (!empty($params['hasYoutubeKey'])) {
            $sql .= "AND t.youtube_key <> '' ";
        }

        if (!empty($params['auteur'])) {
            $sql .= "AND (t.auteur LIKE :auteur OR t.artiste LIKE :auteur) ";
            $p[':auteur'] = '%'.$params['auteur'].'%';
        }
        if (!empty($params['album'])) {
            $sql .= "AND t.album LIKE :album ";
            $p[':album'] = '%'.$params['album'].'%';
            $orderBy .= ", t.numero ASC ";
        } else {
            $orderBy .= ", t.titre ASC ";
        }
        if (!empty($params['genre'])) {
            $sql .= "AND t.genre = :genre ";
            $p[':genre'] = $params['genre'];
        }
        if (!empty($params['note'])) {
            $sql .= "AND t.extra_note >=:note ";
            $p[':note'] = $params['note'];
        }
        if (!empty($params['annee'])) {
            $annee = $params['annee'];
            if (substr_count($annee, '-')) {
                $annee1 = (explode('-', $annee)[0] && is_numeric(explode('-', $annee)[0])) ? explode('-', $annee)[0] : 1;
                $annee2 = (explode('-', $annee)[1] && is_numeric(explode('-', $annee)[1])) ? explode('-', $annee)[1] : date('Y');
                if ($annee1 && $annee2) {
                    $sql .= "AND t.annee >= :annee1 AND t.annee <= :annee2 ";
                    $p[':annee1'] = $annee1;
                    $p[':annee2'] = $annee2;
                }
            } elseif (is_numeric($annee)) {
                $sql .= "AND t.annee = :annee ";
                $p[':annee'] = $params['annee'];
            }
        }

        if (!empty($params['search'])) {
            $sql .= "AND (t.auteur LIKE :search OR t.titre LIKE :search OR t.album LIKE :search OR t.artiste LIKE :search) ";
            $p[':search'] = '%'.$params['search'].'%';
        }

        $rawSQL = $sql;
        $rawSQL .= $orderBy ? 'ORDER BY true ' . $orderBy : '';

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($rawSQL);

        return $stmt->executeQuery($p)->fetchAllAssociative();
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
