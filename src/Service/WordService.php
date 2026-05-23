<?php

namespace App\Service;

use App\Entity\ActeGenere;
use App\Entity\CabinetConfig;
use App\Entity\Facture;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class WordService
{
    public function genererFactureDocx(Facture $facture, CabinetConfig $config): string
    {
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setUpdateFields(true);

        // Style global
        $phpWord->addFontStyle('titre', ['name' => 'Calibri', 'size' => 16, 'bold' => true, 'color' => '1E3A5F']);
        $phpWord->addFontStyle('sousTitre', ['name' => 'Calibri', 'size' => 11, 'bold' => true]);
        $phpWord->addFontStyle('normal', ['name' => 'Calibri', 'size' => 10]);
        $phpWord->addParagraphStyle('centre', ['alignment' => Jc::CENTER]);

        $section = $phpWord->addSection([
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 1000,
            'marginRight' => 1000,
        ]);

        // En-tête cabinet
        $section->addText($config->getNom(), 'titre', 'centre');
        if ($config->getAdresse()) {
            $section->addText($config->getAdresse() . ' - ' . $config->getCodePostal() . ' ' . $config->getVille(), 'normal', 'centre');
        }
        $section->addText('Tél : ' . ($config->getTelephone() ?? '') . ' | Email : ' . ($config->getEmail() ?? ''), 'normal', 'centre');
        $section->addTextBreak(1);

        // Titre document
        $section->addText(strtoupper($facture->getTypeDocument()->value) . ' N° ' . $facture->getNumero(), 'titre', 'centre');
        $section->addText('Date : ' . $facture->getDateEmission()->format('d/m/Y'), 'normal', 'centre');
        $section->addTextBreak(2);

        // Client
        $section->addText('DESTINATAIRE', 'sousTitre');
        $section->addText($facture->getClient()?->getNomComplet() ?? '', 'normal');
        if ($facture->getClient()?->getAdresse()) {
            $section->addText($facture->getClient()->getAdresse(), 'normal');
        }
        $section->addTextBreak(2);

        // Tableau des lignes
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
        $table->addRow(400);
        foreach (['Description', 'Qté', 'Prix unitaire', 'Total HT'] as $header) {
            $cell = $table->addCell(null, ['bgColor' => '1E3A5F']);
            $cell->addText($header, ['bold' => true, 'color' => 'FFFFFF', 'name' => 'Calibri', 'size' => 10]);
        }

        foreach ($facture->getLignes() as $ligne) {
            $table->addRow();
            $table->addCell(5000)->addText($ligne->getDescription(), 'normal');
            $table->addCell(1000)->addText($ligne->getQuantite(), 'normal');
            $table->addCell(2000)->addText(number_format((float)$ligne->getPrixUnitaire(), 2, ',', ' ') . ' €', 'normal');
            $table->addCell(2000)->addText(number_format($ligne->getMontantTotal(), 2, ',', ' ') . ' €', 'normal');
        }
        $section->addTextBreak(1);

        // Totaux
        $section->addText('Montant HT : ' . number_format((float)$facture->getMontantHt(), 2, ',', ' ') . ' €', 'normal');
        $section->addText('TVA (' . $facture->getTauxTva() . '%) : ' . number_format($facture->getMontantTtc() - (float)$facture->getMontantHt(), 2, ',', ' ') . ' €', 'normal');
        $section->addText('TOTAL TTC : ' . number_format($facture->getMontantTtc(), 2, ',', ' ') . ' €', 'sousTitre');

        if ((float)$facture->getProvision() > 0) {
            $section->addText('Provision versée : ' . number_format((float)$facture->getProvision(), 2, ',', ' ') . ' €', 'normal');
        }
        $section->addText('SOLDE RESTANT DÛ : ' . number_format($facture->getSoldeRestant(), 2, ',', ' ') . ' €', ['name' => 'Calibri', 'size' => 12, 'bold' => true, 'color' => 'C0392B']);

        // Mode de règlement
        $section->addTextBreak(2);
        $section->addText('Mode de règlement : ' . $facture->getModeReglement()->value, 'normal');

        // Sauvegarder en mémoire
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function ajouterMarkdown(Section $section, string $contenu): void
    {
        $lignes = explode("\n", $contenu);
        $numOl  = 0;

        foreach ($lignes as $ligne) {
            $trim = rtrim($ligne);

            if ($trim === '') {
                $section->addTextBreak(1);
                $numOl = 0;
                continue;
            }

            if (preg_match('/^-{3,}$/', $trim)) {
                $section->addText(str_repeat('─', 55), ['name' => 'Calibri', 'size' => 8, 'color' => 'AAAAAA']);
                $numOl = 0;
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.+)$/', $trim, $m)) {
                $sizes = [1 => 16, 2 => 13, 3 => 12];
                $size  = $sizes[strlen($m[1])] ?? 12;
                $this->ajouterLigneMarkdown($section, $m[2], ['name' => 'Calibri', 'size' => $size, 'bold' => true]);
                $numOl = 0;
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trim, $m)) {
                $this->ajouterLigneMarkdown($section, '• ' . $m[1], ['name' => 'Calibri', 'size' => 11]);
                $numOl = 0;
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trim, $m)) {
                $numOl++;
                $this->ajouterLigneMarkdown($section, $numOl . '. ' . $m[1], ['name' => 'Calibri', 'size' => 11]);
                continue;
            }

            $numOl = 0;
            $this->ajouterLigneMarkdown($section, $trim, ['name' => 'Calibri', 'size' => 11]);
        }
    }

    private function ajouterLigneMarkdown(Section $section, string $texte, array $styleBase): void
    {
        $parts = preg_split('/(\*\*[^*]+\*\*|\*[^*]+\*)/', $texte, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) <= 1) {
            $section->addText($texte, $styleBase);
            return;
        }

        $run = $section->addTextRun();
        foreach ($parts as $part) {
            if ($part === '') continue;
            if (preg_match('/^\*\*(.+)\*\*$/', $part, $m)) {
                $run->addText($m[1], array_merge($styleBase, ['bold' => true]));
            } elseif (preg_match('/^\*(.+)\*$/', $part, $m)) {
                $run->addText($m[1], array_merge($styleBase, ['italic' => true]));
            } else {
                $run->addText($part, $styleBase);
            }
        }
    }

    public function genererActeDocx(ActeGenere $acte, CabinetConfig $config): string
    {
        $phpWord = new PhpWord();
        $phpWord->addFontStyle('titre', ['name' => 'Calibri', 'size' => 16, 'bold' => true]);
        $phpWord->addFontStyle('normal', ['name' => 'Calibri', 'size' => 11]);
        $phpWord->addParagraphStyle('centre', ['alignment' => Jc::CENTER]);

        $section = $phpWord->addSection([
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 1000,
            'marginRight' => 1000,
        ]);

        $section->addText($config->getNom(), 'titre', 'centre');
        $section->addTextBreak(2);
        $section->addText($acte->getNom() ?? 'Document juridique', 'titre', 'centre');
        $section->addText('Généré le ' . $acte->getDateGeneration()->format('d/m/Y'), 'normal', 'centre');
        $section->addTextBreak(2);

        if ($acte->getContenu()) {
            $this->ajouterMarkdown($section, $acte->getContenu());
        }

        $section->addTextBreak(4);
        $section->addText('Fait à _________________, le ___________________', 'normal');
        $section->addTextBreak(2);
        $section->addText('Signature :', 'normal');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}
