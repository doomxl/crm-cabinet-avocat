<?php

namespace App\Service;

use App\Entity\Facture;
use App\Enum\StatutFactureEnum;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;

class FactureService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FactureRepository $factureRepository,
    ) {}

    public function calculerMontantTtc(Facture $facture): float
    {
        return round((float)$facture->getMontantHt() * (1 + (float)$facture->getTauxTva() / 100), 2);
    }

    public function calculerSoldeRestant(Facture $facture): float
    {
        $ttc = $this->calculerMontantTtc($facture);
        $paye = 0.0;
        foreach ($facture->getPaiements() as $paiement) {
            $paye += (float)$paiement->getMontant();
        }
        return round($ttc - $paye - (float)$facture->getProvision(), 2);
    }

    public function mettreAJourStatut(Facture $facture): void
    {
        $solde = $this->calculerSoldeRestant($facture);
        $ttc = $this->calculerMontantTtc($facture);
        $paye = $ttc - $solde;

        if ($facture->getStatut() === StatutFactureEnum::Annulee) {
            return;
        }

        if ($solde <= 0) {
            $facture->setStatut(StatutFactureEnum::Payee);
        } elseif ($paye > 0 && $solde > 0) {
            $facture->setStatut(StatutFactureEnum::Partiellement_payee);
        } else {
            $now = new \DateTimeImmutable();
            $dateEmission = $facture->getDateEmission();
            $joursEcoules = $now->diff($dateEmission)->days;
            if ($joursEcoules > 30) {
                $facture->setStatut(StatutFactureEnum::En_retard);
            } else {
                $facture->setStatut(StatutFactureEnum::En_cours);
            }
        }
    }

    public function genererNumero(): string
    {
        $annee = date('Y');
        $prefix = 'FA-' . $annee . '-';

        // Trouver le dernier numéro de l'année
        $derniere = $this->entityManager->getRepository(Facture::class)
            ->createQueryBuilder('f')
            ->where('f.numero LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->orderBy('f.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($derniere) {
            $parts = explode('-', $derniere->getNumero());
            $seq = (int)end($parts) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    public function recalculerMontantHt(Facture $facture): void
    {
        $total = 0.0;
        foreach ($facture->getLignes() as $ligne) {
            $total += (float)$ligne->getQuantite() * (float)$ligne->getPrixUnitaire();
        }
        $facture->setMontantHt(number_format($total, 2, '.', ''));
    }
}
