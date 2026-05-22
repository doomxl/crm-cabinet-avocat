<?php

namespace App\Repository;

use App\Entity\EcritureComptable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EcritureComptableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EcritureComptable::class);
    }

    public function findByAnnee(int $annee): array
    {
        return $this->createQueryBuilder('e')
            ->where('YEAR(e.date) = :annee')
            ->setParameter('annee', $annee)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTotauxParCategorie(int $annee): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.categorie, e.type, SUM(e.montant) as total')
            ->where('YEAR(e.date) = :annee')
            ->setParameter('annee', $annee)
            ->groupBy('e.categorie, e.type')
            ->getQuery()
            ->getResult();
    }

    public function getTotauxParMois(int $annee): array
    {
        return $this->createQueryBuilder('e')
            ->select('MONTH(e.date) as mois, e.type, SUM(e.montant) as total')
            ->where('YEAR(e.date) = :annee')
            ->setParameter('annee', $annee)
            ->groupBy('MONTH(e.date), e.type')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(?string $type = null, ?string $categorie = null, ?int $annee = null): array
    {
        $qb = $this->createQueryBuilder('e');
        if ($type) {
            $qb->andWhere('e.type = :type')->setParameter('type', $type);
        }
        if ($categorie) {
            $qb->andWhere('e.categorie = :categorie')->setParameter('categorie', $categorie);
        }
        if ($annee) {
            $qb->andWhere('YEAR(e.date) = :annee')->setParameter('annee', $annee);
        }
        return $qb->orderBy('e.date', 'DESC')->getQuery()->getResult();
    }

    public function save(EcritureComptable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EcritureComptable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
