<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getRandomVideos(
        int $categorie = null,
        int $genre = null,
        string $periode = '0'
    ): array
    {
        $q = $this->getVideosQuery($categorie, $genre, $periode);
        $videos = $q->getQuery()->getResult();
        for ($i = 0; $i < 5; $i++) {
            shuffle($videos);
        }

        return $videos;
    }

    public function getPaginatedVideos(
        int $categorie,
        int $genre,
        string $periode,
        int $page,
        int $maxperpage
    ) : Paginator
    {
        $q = $this->getVideosQuery($categorie, $genre, $periode);

        $q->setFirstResult(($page - 1) * $maxperpage)->setMaxResults($maxperpage);

        return new Paginator($q);
    }

    private function getVideosQuery(int $categorie = null , int $genre = null, string $periode = '0'): QueryBuilder
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->where('a.priorite= :priorite')
            ->setParameter('priorite', 1)
            ->andWhere('a.published = true')
            ->orderBy('a.id', 'DESC')
            ->from('App\Entity\Video', 'a');

        if ($categorie) {
            $q->andWhere('a.categorie= :categorie')->setParameter('categorie', $categorie);
        }

        if ($genre) {
            $q->andWhere('a.genre= :genre')->setParameter('genre', $genre);
        }

        if ($periode) {
            $q->andWhere('a.periode= :periode')->setParameter('periode', $periode);
        }

        return $q;
    }
//    /**
//     * @return Video[] Returns an array of Video objects
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

//    public function findOneBySomeField($value): ?Video
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
