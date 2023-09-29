<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function getTracks(
        string $genre = null,
        float $note = null,
        int $annee = null,
        string $search = null
    ): array
    {
        $q = $this->createQueryBuilder('t')
            ->orderBy('t.titre', 'ASC');

        if ($genre) {
            $q->andWhere('t.genre = :genre')->setParameter(':genre', $genre);
        }
        if ($note) {
            $q->andWhere('t.note = :note')->setParameter(':note', $note);
        }
        if ($annee) {
            if (substr_count($annee, '-')) {
                $annee1 = (explode('-', $annee)[0] && is_numeric(explode('-', $annee)[0])) ? explode('-', $annee)[0] : 1;
                $annee2 = (explode('-', $annee)[1] && is_numeric(explode('-', $annee)[1])) ? explode('-', $annee)[1] : date('Y');
                if ($annee1 && $annee2) {
                    $q->andWhere('t.annee >= :annee1')->setParameter(':annee1', $annee1);
                    $q->andWhere('t.annee <= :annee2')->setParameter(':annee2', $annee2);
                }
            } elseif (is_numeric($annee)) {
                $q->andWhere('a.annee = :annee')->setParameter('annee', $annee);
            }
        }

        if ($search) {
            $orModule = $q->expr()
                ->orx()
                ->add($q->expr()
                    ->like('t.auteur', ':search'))
                ->add($q->expr()
                    ->like('t.titre', ':search'))
                ->add($q->expr()
                    ->like('t.album', ':search'))
                ->add($q->expr()
                    ->like('t.artiste', ':search'));

            $q->andWhere($orModule)
                ->setParameter(':search', '%' . $search . '%');
        }

        return $q->getQuery()->getResult();
    }

    public function getTracksByAlbum(string $artiste, string $album): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.numero', 'ASC')
            ->andWhere('t.artiste =  :artiste')
            ->setParameter(':artiste', $artiste)
            ->andWhere('t.album = :album')
            ->setParameter(':album', $album)
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

        if ($sort !== 'annee')
            $qb->addOrderBy('t.annee', $dir);
        if ($sort !== 'album')
            $qb->addOrderBy('t.album', $dir);
        if ($sort !== 'auteur')
            $qb->addOrderBy('t.auteur', $dir);
        if ($sort !== 'artiste')
            $qb->addOrderBy('t.artiste', $dir);

        $qb->addOrderBy('t.numero', $dir);

        if ($sort !== 'titre')
            $qb->addOrderBy('t.titre', $dir);

        if ($max) {
            $qb->setMaxResults($max)
                ->setFirstResult($page * $max);
        }

        return $qb->getQuery()->getResult();
    }

    public function getTracksByAlbumsAjax(
        array $data,
        int $page = 0,
        int $max = null,
        string $sort = 'album',
        string $dir = 'ASC',
    ): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->groupBy("t.album")
            ->addGroupBy("t.artiste");

        if (!empty($data['query'])) {
            $qb->andWhere('t.auteur like :query OR t.artiste like :query OR t.titre like :query OR t.album like :query')
                ->setParameter('query', "%" . $data['query'] . "%");
        }

        $qb->orderBy('t.' . $sort, $dir);

        if ($sort !== 'annee')
            $qb->addOrderBy('t.annee', $dir);
        if ($sort !== 'album')
            $qb->addOrderBy('t.album', $dir);
        if ($sort !== 'artiste')
            $qb->addOrderBy('t.artiste', $dir);

        if ($max) {
            $qb->setMaxResults($max)
                ->setFirstResult($page * $max);
        }

        return $qb->getQuery()->getResult();
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
