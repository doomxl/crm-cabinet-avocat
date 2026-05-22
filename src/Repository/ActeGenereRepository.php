<?php

namespace App\Repository;

use App\Entity\ActeGenere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActeGenereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActeGenere::class);
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('a.dateGeneration', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(ActeGenere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActeGenere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
