<?php

namespace App\Repository\MusicCollection;

use App\Entity\MusicCollection\VTrack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            ->where('vt.note >= :note')
            ->orWhere('vt.dataOrigin = :origin')
            ->setParameter('note', $note)
            ->setParameter('origin', 'radial')
            ->getQuery()->getResult();

        for ($i = 0; $i < 5; $i++) {
            shuffle($vTracks);
        }

        return $vTracks;
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
