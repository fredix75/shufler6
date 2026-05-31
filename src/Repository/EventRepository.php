<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByDateIntervall(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.date > :start')
            ->andWhere('e.date <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.type', 'ASC')
            ->orderBy('e.sousType', 'ASC')
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
