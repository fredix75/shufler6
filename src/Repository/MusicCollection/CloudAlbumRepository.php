<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\CloudAlbum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CloudAlbum>
 */
class CloudAlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CloudAlbum::class);
    }

    public function getAlbumsAjax(
        array $data,
        int $page = 0,
        int $max = null,
        string $sort = 'name',
        string $dir = 'ASC'
    ): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($data['query'])) {
            $qb->andWhere('a.auteur like :query OR a.name like :query')
                ->setParameter(':query', "%" . $data['query'] . "%");
        }

        $qb->orderBy('a.' . $sort, $dir);

        if ($sort !== 'annee') {
            $qb->addOrderBy('a.annee', $dir);
        }

        if ($sort !== 'auteur') {
            $qb->addOrderBy('a.auteur', $dir);
        }

        if ($sort !== 'name')
            $qb->addOrderBy('a.name', $dir);

        if ($max) {
            $qb->setMaxResults($max)
                ->setFirstResult($page * $max);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return CloudAlbum[] Returns an array of CloudAlbum objects
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

    //    public function findOneBySomeField($value): ?CloudAlbum
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
