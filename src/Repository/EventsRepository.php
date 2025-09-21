<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }

    /**
     * @return Events[] Returns an array of Events objects
     */
    public function findById($value): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByStartDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.start_date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneById($value): ?Events
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    

    public function findRecommendedEvents(User $user)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('App\Entity\UserEventPreference', 'p', 'WITH', 'p.category = e.category')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.preference_score', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

}
