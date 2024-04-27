<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Track>
 *
 * @method Track|null find($id, $lockMode = null, $lockVersion = null)
 * @method Track|null findOneBy(array $criteria, array $orderBy = null)
 * @method Track[]    findAll()
 * @method Track[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function save(Track $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Track $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTracks(array $params = []): array
    {
        $sql= "SELECT t.*, t.youtube_key as youtubeKey, a.picture FROM track t LEFT JOIN album a ON a.name = t.album AND a.auteur = t.artiste WHERE 1 ";
        $orderBy = "";
        $p = [];

        if (!empty($params['hasYoutubeKey'])) {
            $sql .= "AND t.youtube_key <> '' ";
        }

        if (!empty($params['auteur'])) {
            $sql .= "AND t.auteur LIKE :auteur OR t.artiste LIKE :auteur ";
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
            $sql .= "AND t.note >=:note ";
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

    public function getTracksByAlbum(string $artiste, string $album): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.artiste =  :artiste')
            ->setParameter(':artiste', $artiste)
            ->andWhere('t.album = :album')
            ->setParameter(':album', $album)
            ->orderBy('t.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTracksAjax(
        array $data,
        int $page = 0,
        int $max = null,
        string $sort = 'titre',
        string $dir = 'ASC'
    ): array
    {
        $qb = $this->createQueryBuilder('t');

        if (!empty($data['query'])) {
            $qb->andWhere('t.auteur like :query OR t.artiste like :query OR t.titre like :query OR t.album like :query')
                ->setParameter(':query', "%" . $data['query'] . "%");
        }

        $qb->orderBy('t.' . $sort, $dir);

        if ($sort !== 'annee') {
            $qb->addOrderBy('t.annee', $dir);
        }

        if ($sort !== 'album') {
            $qb->addOrderBy('t.album', $dir);
        }

        if ($sort !== 'auteur') {
            $qb->addOrderBy('t.auteur', $dir);
        }

        if ($sort !== 'artiste') {
            $qb->addOrderBy('t.artiste', $dir);
        }

        $qb->addOrderBy('t.numero', $dir);

        if ($sort !== 'titre')
            $qb->addOrderBy('t.titre', $dir);

        if ($max) {
            $qb->setMaxResults($max)
                ->setFirstResult($page * $max);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws Exception
     */
    public function getTracksByAlbumsAjax(
        array $data,
        int $page = 0,
        int $max = null,
        string $sort = 'album',
        string $dir = 'ASC',
    ): array
    {
        $params[':sort'] = $sort;
        $params[':dir'] = $dir;
        $conn = $this->getEntityManager()->getConnection();
        $rawSQL = 'SELECT t.album, t.artiste, a.youtube_key as youtubeKey, json_arrayagg(t.annee) as annees, json_arrayagg(t.genre) as genres FROM track t';
        $rawSQL .= ' JOIN album a on a.auteur=t.artiste and a.name=t.album';
        if (!empty($data['query'])) {
            $rawSQL .= ' WHERE artiste like "%'.$data['query'].'%" OR album like "%'.$data['query'].'%"';
            $params[':query'] = '%'.$data['query'].'%';
        }
        $rawSQL .= " GROUP BY album, artiste, a.youtube_key ORDER BY $sort $dir";

        // TODO Gérer les params à bind (j s p pqoi ca marche pas)

        if ($sort !== 'album') {
            $rawSQL .= " ,album ".$dir;
        }
        if ($sort !== 'artiste') {
            $rawSQL .= " ,artiste ".$dir;
        }
        if ($max) {
            $offset = $page ? $page * $max - 1 : 0;
            $rawSQL .= " LIMIT $max OFFSET $offset ";
            $params[':max'] = $max;
            $params[':offset'] = $offset;
        }

        $stmt = $conn->prepare($rawSQL);

        foreach($params as $param => $value) {
            //$stmt->bindValue($param, $value, ParameterType::STRING);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    function resetChecks() {
        $conn = $this->getEntityManager()->getConnection();
        $rawSQL = 'UPDATE track set is_check = FALSE WHERE TRUE';
        $conn->executeQuery($rawSQL);
    }

    function searchAjax(string $search): array
    {
        $auteurs = $this->createQueryBuilder('t')
            ->select('t.auteur')
            ->andWhere('t.auteur like :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.auteur', 'ASC')
            ->groupBy('t.auteur')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $titres = $this->createQueryBuilder('t')
            ->select('t.titre')
            ->andWhere('t.titre like :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.titre', 'ASC')
            ->groupBy('t.titre')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $albums = $this->createQueryBuilder('t')
            ->select('t.album')
            ->andWhere('t.album like :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.album', 'ASC')
            ->groupBy('t.album')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $suggestions = [];

        foreach ($auteurs as $auteur) {
            $suggestions[] = $auteur['auteur'];
        }
        foreach ($titres as $titre) {
            if (\in_array($titre['titre'], $suggestions)) {
                continue;
            }
            $suggestions[] = $titre['titre'];
        }
        foreach ($albums as $album) {
            if (\in_array($album['album'], $suggestions)) {
                continue;
            }
            $suggestions[] = $album['album'];
        }

        return $suggestions;
    }

    public function getGenres(): array
    {
        return $this->createQueryBuilder('t')->select('distinct t.genre')
            ->orderBy('t.genre')
            ->getQuery()->getResult();
    }

    public function getTracksByCountry(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $rawSql = "SELECT count(*), pays FROM track GROUP BY pays ORDER BY pays";
        $stmt = $conn->prepare($rawSql);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

//    /**
//     * @return Track[] Returns an array of Track objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Track
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
