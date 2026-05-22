<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\EcritureComptable;
use League\Csv\Writer;

class ExportService
{
    public function exporterJournalCsv(array $ecritures): string
    {
        $csv = Writer::createFromString();
        $csv->setDelimiter(';');

        $csv->insertOne(['Date', 'Type', 'Catégorie', 'Libellé', 'Montant (€)', 'Mode règlement', 'Référence', 'N° Facture']);

        foreach ($ecritures as $ecriture) {
            /** @var EcritureComptable $ecriture */
            $csv->insertOne([
                $ecriture->getDate()->format('d/m/Y'),
                $ecriture->getType()->label(),
                $ecriture->getCategorie()->value,
                $ecriture->getLibelle(),
                number_format((float)$ecriture->getMontant(), 2, ',', ' '),
                $ecriture->getModeReglement()?->value ?? '',
                $ecriture->getReference() ?? '',
                $ecriture->getFacture()?->getNumero() ?? '',
            ]);
        }

        return $csv->toString();
    }

    public function exporterRecap2035(array $ecritures): string
    {
        $csv = Writer::createFromString();
        $csv->setDelimiter(';');

        $csv->insertOne(['Déclaration 2035 - Récapitulatif BNC']);
        $csv->insertOne(['']);
        $csv->insertOne(['Code', 'Libellé catégorie', 'Type', 'Total (€)']);

        // Regrouper par catégorie
        $totaux = [];
        foreach ($ecritures as $ecriture) {
            /** @var EcritureComptable $ecriture */
            $code = $ecriture->getCategorie()->value;
            $type = $ecriture->getType()->value;
            $key = $code . '_' . $type;
            if (!isset($totaux[$key])) {
                $totaux[$key] = [
                    'code' => $code,
                    'libelle' => $ecriture->getCategorie()->label(),
                    'type' => $ecriture->getType()->label(),
                    'total' => 0.0,
                ];
            }
            $totaux[$key]['total'] += (float)$ecriture->getMontant();
        }

        ksort($totaux);
        $totalRecettes = 0.0;
        $totalDepenses = 0.0;

        foreach ($totaux as $t) {
            $csv->insertOne([
                $t['code'],
                $t['libelle'],
                $t['type'],
                number_format($t['total'], 2, ',', ' '),
            ]);
            if ($t['type'] === 'Recette') {
                $totalRecettes += $t['total'];
            } else {
                $totalDepenses += $t['total'];
            }
        }

        $csv->insertOne(['']);
        $csv->insertOne(['TOTAL RECETTES', '', '', number_format($totalRecettes, 2, ',', ' ')]);
        $csv->insertOne(['TOTAL DÉPENSES', '', '', number_format($totalDepenses, 2, ',', ' ')]);
        $csv->insertOne(['BÉNÉFICE NET', '', '', number_format($totalRecettes - $totalDepenses, 2, ',', ' ')]);

        return $csv->toString();
    }

    public function exporterClientsCsv(array $clients): string
    {
        $csv = Writer::createFromString();
        $csv->setDelimiter(';');

        $csv->insertOne(['Code client', 'Type', 'Nom', 'Prénom', 'Raison sociale', 'Email', 'Téléphone', 'Adresse', 'Code postal', 'Ville', 'SIRET', 'Date création']);

        foreach ($clients as $client) {
            /** @var Client $client */
            $csv->insertOne([
                $client->getCodeClient() ?? '',
                $client->getTypeClient()->value,
                $client->getNom(),
                $client->getPrenom() ?? '',
                $client->getIntitule() ?? '',
                $client->getEmail() ?? '',
                $client->getTelephone() ?? '',
                $client->getAdresse() ?? '',
                $client->getCodePostal() ?? '',
                $client->getVille() ?? '',
                $client->getSiret() ?? '',
                $client->getCreatedAt()->format('d/m/Y'),
            ]);
        }

        return $csv->toString();
    }
}
