<?php

namespace App\Repository;

use App\Entity\Chronologie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChronologieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chronologie::class);
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Chronologie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Chronologie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
