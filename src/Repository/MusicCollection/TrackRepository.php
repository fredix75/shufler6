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
        ?int $max = null,
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
        ?int $max = null,
        string $sort = 'album',
        string $dir = 'ASC',
    ): array
    {
        $params[':sort'] = $sort;
        $params[':dir'] = $dir;
        $conn = $this->getEntityManager()->getConnection();
        $rawSQL = 'SELECT a.id as id, t.album, t.artiste, a.youtube_key as youtubeKey, json_arrayagg(t.annee) as annees, json_arrayagg(t.genre) as genres FROM piece t';
        $rawSQL .= ' JOIN album a on a.auteur=t.artiste and a.name=t.album WHERE t.data_type = 1';
        if (!empty($data['query'])) {
            $rawSQL .= ' AND (artiste like "%'.$data['query'].'%" OR album like "%'.$data['query'].'%")';
            $params[':query'] = '%'.$data['query'].'%';
        }
        $rawSQL .= " GROUP BY id, album, artiste, a.youtube_key ORDER BY $sort $dir";

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
        $rawSQL = 'UPDATE piece set is_check = FALSE WHERE data_type = 1';
        $conn->executeQuery($rawSQL);
    }

    //TODO A modifier pour élargir la recherche
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

    //TODO A modifier pour élargir ??
    public function getTracksByCountry(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $rawSql = "SELECT count(*), pays FROM piece GROUP BY pays ORDER BY pays";
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
