<?php

namespace App\Repository;

use App\Entity\PartieAdverse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PartieAdverseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartieAdverse::class);
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchByNom(string $nom, string $prenom = ''): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.dossier', 'd')
            ->addSelect('d')
            ->where('p.nom LIKE :nom OR p.entreprise LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%');
        if ($prenom) {
            $qb->andWhere('p.prenom LIKE :prenom')->setParameter('prenom', '%' . $prenom . '%');
        }
        return $qb->getQuery()->getResult();
    }

    public function save(PartieAdverse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PartieAdverse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
