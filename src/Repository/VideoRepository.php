<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
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

    /**
     * @throws NonUniqueResultException
     */
    public function find(mixed $id, $lockMode = null, $lockVersion = null): ?Video
    {
        $q = $this->createQueryBuilder('a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('a.moods', 'moods')
            ->addSelect('moods');

        return $q->getQuery()->getOneOrNullResult();
    }

    public function getRandomVideos(
        ?string $search = null,
        ?int $categorie = null,
        ?int $genre = null,
        ?string $periode = '0',
        ?string $plateforme = null
    ): array
    {
        $q = $this->getVideosQuery($categorie, $genre, $periode, $search, $plateforme);
        $videos = $q->getQuery()->getResult();
        for ($i = 0; $i < 5; $i++) {
            shuffle($videos);
        }

        return $videos;
    }

    public function getVideosQuery(
        ?int $categorie = null,
        ?int $genre = null,
        ?string $periode = '0',
        ?string $search = null,
        ?string $plateforme = null
    ): QueryBuilder
    {
        $q = $this->createQueryBuilder('v')
            ->addSelect('mood')
            ->leftJoin('v.moods', 'mood')
            ->where('v.priorite= :priorite')
            ->setParameter('priorite', 1)
            ->andWhere('v.published = true');
        if ($plateforme) {
            $q->andWhere('v.lien like :plateforme')
                ->setParameter('plateforme', '%'.$plateforme.'%');
        }
            $q->orderBy('v.id', \SortDirection::Descending);

        if ($categorie) {
            $q->andWhere('v.categorie= :categorie')
                ->setParameter('categorie', $categorie);
        }

        if ($genre) {
            $q->andWhere('v.genre= :genre')
                ->setParameter('genre', $genre);
        }

        if ($periode) {
            $q->andWhere('v.periode= :periode')
                ->setParameter('periode', $periode);
        }

        if ($search) {
            $q->andWhere("v.auteur like :search OR v.titre like :search OR v.chapo like :search")
                ->setParameter('search', '%'.$search.'%');
        }

        return $q;
    }

    function searchVideos(
        string $search,
        int $page = 1,
        int $maxperpage = 10
    ): QueryBuilder
    {
        $q = $this->createQueryBuilder('a')
            ->where('a.published= 1');

        $orModule = $q->expr()
            ->orx()
            ->add($q->expr()
                ->like('a.auteur', ':search'))
            ->add($q->expr()
                ->like('a.titre', ':search'))
            ->add($q->expr()
                ->like('a.chapo', ':search'))
            ->add($q->expr()
                ->like('a.annee', ':search'));

        return $q->andWhere($orModule)
            ->setParameter('search', '%' . $search . '%');
    }

    function searchAjax(string $search): array
    {
        $auteurs = $this->createQueryBuilder('a')
            ->select('a.auteur')
            ->where('a.priorite= :priorite')
            ->andWhere('a.published= true')
            ->andWhere('a.auteur like :search OR a.chapo like :search')
            ->setParameter('priorite', 1)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('a.auteur', \SortDirection::Ascending)
            ->groupBy('a.auteur')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $titres = $this->createQueryBuilder('a')
            ->select('a.titre')
            ->where('a.priorite= :priorite')
            ->andWhere('a.published= true')
            ->andWhere('a.titre like :search')
            ->setParameter('priorite', 1)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('a.titre', \SortDirection::Ascending)
            ->groupBy('a.titre')
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

        return $suggestions;
    }

    public function getTrashQuery($page = 1, $maxperpage = 10): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.priorite != :priorite')
            ->setParameter('priorite', 1)
            ->orWhere('a.published is null')
            ->orWhere('a.published = false')
            ->orderBy('a.published', \SortDirection::Descending)
            ->addOrderBy('a.priorite', \SortDirection::Ascending)
            ->addOrderBy('a.id', \SortDirection::Descending);
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
