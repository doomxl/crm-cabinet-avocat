<?php

namespace App\Repository;

use App\Entity\Rendezvous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RendezvousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvous::class);
    }

    public function findByDate(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->addSelect('c')
            ->where('r.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('r.heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySemaine(string $semaine): array
    {
        // semaine format: 2024-W03
        [$year, $week] = explode('-W', $semaine);
        $debut = new \DateTimeImmutable();
        $debut = $debut->setISODate((int)$year, (int)$week, 1);
        $fin = $debut->modify('+6 days');
        return $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->addSelect('c')
            ->where('r.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMois(string $mois): array
    {
        // mois format: 2024-05
        $debut = new \DateTimeImmutable($mois . '-01');
        $fin   = $debut->modify('last day of this month');
        return $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->addSelect('c')
            ->where('r.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findToday(): array
    {
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        return $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->addSelect('c')
            ->where('r.date = :today')
            ->setParameter('today', $today)
            ->orderBy('r.heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAVenir(int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->addSelect('c')
            ->where('r.date >= :today')
            ->setParameter('today', (new \DateTimeImmutable())->format('Y-m-d'))
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.heure', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function save(Rendezvous $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rendezvous $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
