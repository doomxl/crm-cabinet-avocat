<?php

namespace App\Repository;

use App\Entity\Echeance;
use App\Enum\StatutEcheanceEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EcheanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Echeance::class);
    }

    public function findUrgentes(int $jours = 7): array
    {
        $limit = new \DateTimeImmutable('+' . $jours . ' days');
        return $this->createQueryBuilder('e')
            ->leftJoin('e.dossier', 'd')
            ->addSelect('d')
            ->where('e.date <= :limit')
            ->andWhere('e.statut = :statut')
            ->setParameter('limit', $limit->format('Y-m-d'))
            ->setParameter('statut', StatutEcheanceEnum::A_venir->value)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(?string $statut = null, ?int $dossierId = null, ?int $urgenceJours = null): array
    {
        $qb = $this->createQueryBuilder('e')->leftJoin('e.dossier', 'd')->addSelect('d');
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }
        if ($dossierId) {
            $qb->andWhere('e.dossier = :dossierId')->setParameter('dossierId', $dossierId);
        }
        if ($urgenceJours !== null) {
            $limit = new \DateTimeImmutable('+' . $urgenceJours . ' days');
            $qb->andWhere('e.date <= :limit')->setParameter('limit', $limit->format('Y-m-d'));
        }
        return $qb->orderBy('e.date', 'ASC')->getQuery()->getResult();
    }

    public function countUrgentes(int $jours = 7): int
    {
        $limit = new \DateTimeImmutable('+' . $jours . ' days');
        return (int)$this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.date <= :limit')
            ->andWhere('e.statut = :statut')
            ->setParameter('limit', $limit->format('Y-m-d'))
            ->setParameter('statut', StatutEcheanceEnum::A_venir->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Echeance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Echeance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
