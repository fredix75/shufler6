<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\CloudTrack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CloudTrack>
 */
class CloudTrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CloudTrack::class);
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
            $qb->andWhere('t.auteur like :query OR t.titre like :query')
                ->setParameter(':query', "%" . $data['query'] . "%");
        }

        $qb->orderBy('t.' . $sort, $dir);

        if ($sort !== 'annee') {
            $qb->addOrderBy('t.annee', $dir);
        }

        if ($sort !== 'auteur') {
            $qb->addOrderBy('t.auteur', $dir);
        }

        if ($sort !== 'titre')
            $qb->addOrderBy('t.titre', $dir);

        if ($max) {
            $qb->setMaxResults($max)
                ->setFirstResult($page * $max);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return CloudTrack[] Returns an array of CloudTrack objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CloudTrack
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
