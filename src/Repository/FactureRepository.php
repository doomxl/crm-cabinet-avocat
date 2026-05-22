<?php

namespace App\Repository;

use App\Entity\Facture;
use App\Enum\StatutFactureEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function findImpayees(): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.client', 'c')
            ->addSelect('c')
            ->where('f.statut IN (:statuts)')
            ->setParameter('statuts', [StatutFactureEnum::En_cours->value, StatutFactureEnum::En_retard->value, StatutFactureEnum::Partiellement_payee->value])
            ->orderBy('f.dateEmission', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('f.dateEmission', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getMontantMensuel(\DateTime $date): float
    {
        $debut = \DateTimeImmutable::createFromFormat('Y-m-d', $date->format('Y-m-01'));
        $fin = \DateTimeImmutable::createFromFormat('Y-m-d', $date->format('Y-m-t'));
        $result = $this->createQueryBuilder('f')
            ->select('SUM(f.montantHt * (1 + f.tauxTva / 100))')
            ->where('f.dateEmission BETWEEN :debut AND :fin')
            ->andWhere('f.statut != :annulee')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->setParameter('annulee', StatutFactureEnum::Annulee->value)
            ->getQuery()
            ->getSingleScalarResult();
        return (float)($result ?? 0);
    }

    public function findFiltered(?string $statut = null, ?int $clientId = null, ?int $dossierId = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.client', 'c')
            ->leftJoin('f.dossier', 'd')
            ->addSelect('c', 'd');
        if ($statut) {
            $qb->andWhere('f.statut = :statut')->setParameter('statut', $statut);
        }
        if ($clientId) {
            $qb->andWhere('f.client = :clientId')->setParameter('clientId', $clientId);
        }
        if ($dossierId) {
            $qb->andWhere('f.dossier = :dossierId')->setParameter('dossierId', $dossierId);
        }
        return $qb->orderBy('f.dateEmission', 'DESC')->getQuery()->getResult();
    }

    public function getChiffreAffairesMensuel(int $year): array
    {
        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $debut = \DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%d-%02d-01', $year, $month));
            $fin = $debut->modify('last day of this month');
            $ca = $this->createQueryBuilder('f')
                ->select('SUM(f.montantHt * (1 + f.tauxTva / 100))')
                ->where('f.dateEmission BETWEEN :debut AND :fin')
                ->andWhere('f.statut != :annulee')
                ->setParameter('debut', $debut->format('Y-m-d'))
                ->setParameter('fin', $fin->format('Y-m-d'))
                ->setParameter('annulee', StatutFactureEnum::Annulee->value)
                ->getQuery()
                ->getSingleScalarResult();
            $result[$month] = (float)($ca ?? 0);
        }
        return $result;
    }

    public function save(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
