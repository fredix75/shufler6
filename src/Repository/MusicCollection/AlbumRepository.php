<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function save(Album $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Album $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAlbums(array $params, int $page, int $max): Paginator|array
    {
        $query = $this->createQueryBuilder('a');

        if (!empty($params['search'])) {
            $orModule = $query->expr()
                ->orx()
                ->add($query->expr()
                    ->like('a.auteur', ':search'))
                ->add($query->expr()
                    ->like('a.name', ':search'));

            $query->andWhere($orModule)
                ->setParameter(':search', '%'.$params['search'].'%');
        }

        if (!empty($params['auteur'])) {
            $query->andWhere('a.auteur like :auteur')
                ->setParameter('auteur', '%'.$params['auteur'].'%');
        }

        if (!empty($params['album'])) {
            $query->andWhere('a.name like :album')
            ->setParameter('album', '%'.$params['album'].'%');
        }

        if (!empty($params['genre'])) {
            $query->andWhere('a.genre = :genre')->setParameter(':genre', $params['genre']);
        }

        if (!empty($params['annee'])) {
            $annee = $params['annee'];
            if (substr_count($annee, '-')) {
                $annee1 = (explode('-', $annee)[0] && is_numeric(explode('-', $annee)[0])) ? explode('-', $annee)[0] : 1;
                $annee2 = (explode('-', $annee)[1] && is_numeric(explode('-', $annee)[1])) ? explode('-', $annee)[1] : date('Y');
                if ($annee1 && $annee2) {
                    $query->andWhere('a.annee >= :annee1')->setParameter(':annee1', $annee1);
                    $query->andWhere('a.annee <= :annee2')->setParameter(':annee2', $annee2);
                }
            } elseif (is_numeric($annee)) {
                $query->andWhere('a.annee = :annee')->setParameter('annee', $annee);
            }
        }

        if ($params['random']) {
            $result = $query->getQuery()->getResult();
            shuffle($result);
            return array_slice($result, 0, 50);
        } else {
            $query->orderBy('a.name', 'ASC')
                ->setMaxResults($max)->setFirstResult(($page-1)*$max);
        }

        $query->getQuery()->setHint(CountWalker::HINT_DISTINCT, true);
        $paginator = new Paginator($query, false);
        $paginator->setUseOutputWalkers(false);

        return $paginator;
    }

//    /**
//     * @return Album[] Returns an array of Album objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Album
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
