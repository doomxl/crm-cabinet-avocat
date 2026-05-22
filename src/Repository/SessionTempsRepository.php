<?php

namespace App\Repository;

use App\Entity\SessionTemps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionTempsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionTemps::class);
    }

    public function findParDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('s.debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findSessionEnCours(): ?SessionTemps
    {
        return $this->createQueryBuilder('s')
            ->where('s.fin IS NULL')
            ->orderBy('s.debut', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(SessionTemps $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SessionTemps $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
