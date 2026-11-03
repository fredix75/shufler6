<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\VTrack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends ServiceEntityRepository<VTrack>
 */
class VTrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VTrack::class);
    }

    public function findRandomVTracks(int $note = 4): array
    {
        $vTracks = $this->createQueryBuilder('vt')
            ->where('vt.note >= :note or vt.dataOrigin = :origin')
            ->andWhere('vt.youtubeKey IS NOT NULL')
            ->setParameter('note', $note)
            ->setParameter('origin', 'radial')
            ->getQuery()->getResult();

        for ($i = 0; $i < 5; $i++) {
            shuffle($vTracks);
        }

        return $vTracks;
    }

    public function getVTracks(array $params = [], int $offset = 0, int $max = 100): array
    {
        $builder = $this->createQueryBuilder('vt');

        if (!empty($params['dataOrigin'])) {
            $builder->andWhere('vt.dataOrigin = :dataOrigin')
                ->setParameter('dataOrigin', $params['dataOrigin']);
        }

        if (!empty($params['hasYoutubeKey'])) {
            $builder->andWhere('vt.youtubeKey IS NOT NULL')
            ->andWhere('vt.youtubeKey <> :nope')
            ->setParameter('nope', 'nope');
        }

        if (!empty($params['auteur'])) {
            $builder->andWhere('vt.author LIKE :auteur OR vt.albumArtist LIKE :auteur')
                ->setParameter('auteur', '%' . $params['auteur'] . '%');
        }

        if (!empty($params['album'])) {
            $builder->andWhere('vt.albumName LIKE :album')
                ->setParameter('album', '%' . $params['album'] . '%')
                ->addOrderBy('vt.numero', \SortDirection::Ascending);
        } else {
            $builder->addOrderBy('vt.title', \SortDirection::Ascending);
        }

        if (!empty($params['genres'])) {
            $builder->andWhere('vt.kind IN (:genres)')
                ->setParameter('genres', implode(',', $params['genres']));
        }

        if (!empty($params['annee'])) {
            $annee = $params['annee'];
            if (substr_count($annee, '-')) {
                $annee1 = (explode('-', $annee)[0] && is_numeric(explode('-', $annee)[0])) ? explode('-', $annee)[0] : 1;
                $annee2 = (explode('-', $annee)[1] && is_numeric(explode('-', $annee)[1])) ? explode('-', $annee)[1] : date('Y');
                if ($annee1 && $annee2) {
                    $builder->andWhere('vt.year >= :annee1 AND vt.year <= :annee2')
                        ->setParameter('annee1', $annee1)
                        ->setParameter('annee2', $annee2);
                }
            } elseif (is_numeric($annee)) {
                $builder->andWhere('vt.year = :annee')
                    ->setParameter('annee', $annee);
            }
        }

        if (!empty($params['search'])) {
            $builder->andWhere('vt.title LIKE :search OR vt.author LIKE :search OR vt.albumName LIKE :search OR vt.albumArtist LIKE :search')
                ->setParameter('search', '%' . $params['search'] . '%');
        }

        if (empty($params['random'])) {
            $builder->addOrderBy('vt.author', \SortDirection::Ascending)
                ->addOrderBy('vt.title', \SortDirection::Ascending)
                ->setMaxResults($max)
                ->setFirstResult($offset);
        }

        return $builder->getQuery()->getResult();
    }


//    /**
//     * @return VTrack[] Returns an array of VTrack objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VTrack
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
