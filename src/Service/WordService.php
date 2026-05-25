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
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $margin  = 907; // 16 mm
        $section = $phpWord->addSection([
            'marginTop' => $margin, 'marginBottom' => $margin,
            'marginLeft' => $margin, 'marginRight'  => $margin,
        ]);

        $W    = 10400; // largeur utile en twips
        $navy = '1A2942';

        // ── Styles de police ──────────────────────────────────────────────
        $fCabNom    = ['name' => 'Arial', 'size' => 16, 'bold' => true,  'color' => $navy];
        $fCabInfo   = ['name' => 'Arial', 'size' => 8,  'color' => '555555'];
        $fDocTitre  = ['name' => 'Arial', 'size' => 14, 'bold' => true,  'color' => $navy];
        $fDocInfo   = ['name' => 'Arial', 'size' => 9,  'color' => '666666'];
        $fBadge     = ['name' => 'Arial', 'size' => 8,  'bold' => true,  'color' => 'FFFFFF'];
        $fBlocTitre = ['name' => 'Arial', 'size' => 9,  'bold' => true,  'color' => '888888', 'allCaps' => true];
        $fClientNom = ['name' => 'Arial', 'size' => 11, 'bold' => true,  'color' => $navy,    'allCaps' => true];
        $fAdresse   = ['name' => 'Arial', 'size' => 9,  'allCaps' => true];
        $fContact   = ['name' => 'Arial', 'size' => 8,  'color' => '444444'];
        $fLabel     = ['name' => 'Arial', 'size' => 9,  'color' => '666666'];
        $fValue     = ['name' => 'Arial', 'size' => 9,  'bold' => true];
        $fNormal    = ['name' => 'Arial', 'size' => 9];
        $fSmall     = ['name' => 'Arial', 'size' => 8,  'color' => '666666'];
        $fTH        = ['name' => 'Arial', 'size' => 8,  'bold' => true,  'color' => 'FFFFFF', 'allCaps' => true];
        $fTtc       = ['name' => 'Arial', 'size' => 11, 'bold' => true,  'color' => $navy];
        $fGreen     = ['name' => 'Arial', 'size' => 9,  'color' => '198754'];

        $pR = ['alignment' => Jc::END];
        $pL = ['alignment' => Jc::START];

        // ── Styles de cellule ─────────────────────────────────────────────
        $noBorder  = ['borderTopSize' => 0, 'borderTopColor' => 'FFFFFF', 'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF', 'borderLeftSize' => 0, 'borderLeftColor' => 'FFFFFF', 'borderRightSize' => 0, 'borderRightColor' => 'FFFFFF'];
        $topNavy   = array_merge($noBorder, ['borderTopSize' => 6, 'borderTopColor' => $navy]);
        $greyCell  = ['bgColor' => 'F8F9FA', 'borderSize' => 4, 'borderColor' => 'E9ECEF'];
        $headerBot = array_merge($noBorder, ['borderBottomSize' => 18, 'borderBottomColor' => $navy, 'valign' => 'top']);

        // ═══════════════════════════════════════════════════════════════════
        // EN-TÊTE (2 colonnes)
        // ═══════════════════════════════════════════════════════════════════
        $hTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
        $hTable->addRow(600);

        $hLeft = $hTable->addCell((int)($W * 0.55), $headerBot);
        $hLeft->addText($config->getNom(), $fCabNom);
        if ($config->getAdresse())   $hLeft->addText($config->getAdresse(), $fCabInfo);
        if ($config->getVille())     $hLeft->addText(($config->getCodePostal() ?? '') . ' ' . $config->getVille(), $fCabInfo);
        if ($config->getTelephone()) $hLeft->addText('Tél. : ' . $config->getTelephone(), $fCabInfo);
        if ($config->getEmail())     $hLeft->addText($config->getEmail(), $fCabInfo);
        if ($config->getSiret())     $hLeft->addText('SIRET : ' . $config->getSiret(), $fCabInfo);

        $hRight = $hTable->addCell((int)($W * 0.45), $headerBot);
        $hRight->addText($facture->getTypeDocument()->label(), $fDocTitre, $pR);
        $hRight->addText('N° ' . $facture->getNumero(), $fDocInfo, $pR);
        $hRight->addText('Émise le ' . $facture->getDateEmission()->format('d/m/Y'), $fDocInfo, $pR);
        $badgeRun = $hRight->addTextRun($pR);
        $badgeRun->addText(' ' . mb_strtoupper($facture->getStatut()->label()) . ' ', array_merge($fBadge, ['bgColor' => $navy]));

        $section->addTextBreak(1);

        // ═══════════════════════════════════════════════════════════════════
        // BLOCS DESTINATAIRE / INFORMATIONS
        // ═══════════════════════════════════════════════════════════════════
        $bTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
        $bTable->addRow();

        // — Destinataire
        $bLeft = $bTable->addCell((int)($W * 0.47), array_merge($noBorder, ['valign' => 'top']));
        $bLeft->addText('Destinataire', $fBlocTitre);
        $cTable = $bLeft->addTable(['borderSize' => 4, 'borderColor' => 'E9ECEF', 'cellMargin' => 80]);
        $cTable->addRow();
        $cCell = $cTable->addCell((int)($W * 0.45), $greyCell);
        $cCell->addText(mb_strtoupper($facture->getClient()?->getNomComplet() ?? ''), $fClientNom);
        if ($facture->getClient()?->getAdresse())
            $cCell->addText(mb_strtoupper($facture->getClient()->getAdresse()), $fAdresse);
        if ($facture->getClient()?->getVille()) {
            $cp = trim(($facture->getClient()->getCodePostal() ?? '') . ' ' . $facture->getClient()->getVille());
            $cCell->addText(mb_strtoupper($cp), $fAdresse);
        }
        if ($facture->getClient()?->getEmail())
            $cCell->addText($facture->getClient()->getEmail(), $fContact);
        if ($facture->getClient()?->getTelephone())
            $cCell->addText($facture->getClient()->getTelephone(), $fContact);

        // — Spacer
        $bTable->addCell((int)($W * 0.04), array_merge($noBorder));

        // — Informations
        $bRight = $bTable->addCell((int)($W * 0.49), array_merge($noBorder, ['valign' => 'top']));
        $bRight->addText('Informations', $fBlocTitre);
        $iTable = $bRight->addTable(['borderSize' => 4, 'borderColor' => 'E9ECEF', 'cellMargin' => 60]);
        $infoRows = [
            ['Référence',         $facture->getNumero()],
            ["Date d'émission",   $facture->getDateEmission()->format('d/m/Y')],
            ['Mode de règlement', $facture->getModeReglement()->value],
        ];
        if ($facture->getDossier())
            $infoRows[] = ['Dossier', mb_substr($facture->getDossier()->getTitre(), 0, 35)];

        foreach ($infoRows as [$lbl, $val]) {
            $iTable->addRow();
            $iTable->addCell((int)($W * 0.22), $greyCell)->addText($lbl, $fLabel);
            $iTable->addCell((int)($W * 0.25), $greyCell)->addText($val,  $fValue);
        }

        $section->addTextBreak(1);

        // ═══════════════════════════════════════════════════════════════════
        // TABLEAU DES LIGNES
        // ═══════════════════════════════════════════════════════════════════
        $cols   = [(int)($W * 0.42), (int)($W * 0.10), (int)($W * 0.10), (int)($W * 0.19), (int)($W * 0.19)];
        $lTable = $section->addTable(['borderSize' => 4, 'borderColor' => 'E9ECEF', 'cellMargin' => 80]);
        $lTable->addRow(300);
        foreach (['Description', 'Qté', 'Unité', 'Prix unit. HT', 'Total HT'] as $i => $h) {
            $lTable->addCell($cols[$i], ['bgColor' => $navy, 'borderSize' => 0, 'borderColor' => $navy])
                   ->addText(mb_strtoupper($h), $fTH, $i === 0 ? $pL : $pR);
        }
        $even = false;
        foreach ($facture->getLignes() as $ligne) {
            $bg  = $even ? 'F8F9FA' : 'FFFFFF';
            $even = !$even;
            $rStyle = array_merge($noBorder, ['bgColor' => $bg, 'borderBottomSize' => 4, 'borderBottomColor' => 'E9ECEF']);
            $lTable->addRow();
            $lTable->addCell($cols[0], $rStyle)->addText(mb_strtoupper($ligne->getDescription()), $fNormal, $pL);
            $lTable->addCell($cols[1], $rStyle)->addText((string)$ligne->getQuantite(), $fNormal, $pR);
            $lTable->addCell($cols[2], $rStyle)->addText($ligne->getUnite(), $fNormal, $pR);
            $lTable->addCell($cols[3], $rStyle)->addText(number_format((float)$ligne->getPrixUnitaire(), 2, ',', ' ') . ' €', $fNormal, $pR);
            $lTable->addCell($cols[4], $rStyle)->addText(number_format($ligne->getMontantTotal(), 2, ',', ' ') . ' €', $fValue, $pR);
        }

        $section->addTextBreak(1);

        // ═══════════════════════════════════════════════════════════════════
        // TOTAUX
        // ═══════════════════════════════════════════════════════════════════
        $tOuter = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
        $tOuter->addRow();

        $avCell = $tOuter->addCell((int)($W * 0.55), array_merge($noBorder, ['valign' => 'top']));
        if ($config->getAvocatNom()) {
            $avCell->addText('Avocat', $fBlocTitre);
            $avCell->addText($config->getAvocatNom(), $fNormal);
            if ($config->getAvocatBarreau()) $avCell->addText('Barreau de ' . $config->getAvocatBarreau(), $fNormal);
            if ($config->getAvocatNumero())  $avCell->addText('N° ' . $config->getAvocatNumero(), $fNormal);
        }

        $totW     = (int)($W * 0.45);
        $lw       = (int)($totW * 0.60);
        $vw       = (int)($totW * 0.40);
        $totCell  = $tOuter->addCell($totW, array_merge($noBorder, ['valign' => 'top']));
        $tInner   = $totCell->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMarginTop' => 30, 'cellMarginBottom' => 30, 'cellMarginLeft' => 0, 'cellMarginRight' => 0]);

        // Montant HT
        $tInner->addRow();
        $tInner->addCell($lw, $noBorder)->addText('Montant HT', $fSmall);
        $tInner->addCell($vw, $noBorder)->addText(number_format((float)$facture->getMontantHt(), 2, ',', ' ') . ' €', $fNormal, $pR);

        // TVA
        $tva = fmod((float)$facture->getTauxTva(), 1.0) == 0.0 ? (int)$facture->getTauxTva() : $facture->getTauxTva();
        $tInner->addRow();
        $tInner->addCell($lw, $noBorder)->addText('TVA (' . $tva . ' %)', $fSmall);
        $tInner->addCell($vw, $noBorder)->addText(number_format($facture->getMontantTtc() - (float)$facture->getMontantHt(), 2, ',', ' ') . ' €', $fNormal, $pR);

        // Total TTC
        $tInner->addRow();
        $tInner->addCell($lw, $topNavy)->addText('Total TTC', $fTtc);
        $tInner->addCell($vw, $topNavy)->addText(number_format($facture->getMontantTtc(), 2, ',', ' ') . ' €', $fTtc, $pR);

        // Provision
        if ((float)$facture->getProvision() > 0) {
            $tInner->addRow();
            $tInner->addCell($lw, $noBorder)->addText('Provision versée', $fSmall);
            $tInner->addCell($vw, $noBorder)->addText('- ' . number_format((float)$facture->getProvision(), 2, ',', ' ') . ' €', $fGreen, $pR);
        }

        // Paiements reçus
        $paye = $facture->getMontantPaye();
        if ($paye > 0) {
            $tInner->addRow();
            $tInner->addCell($lw, $noBorder)->addText('Paiements reçus', $fSmall);
            $tInner->addCell($vw, $noBorder)->addText('- ' . number_format($paye, 2, ',', ' ') . ' €', $fGreen, $pR);
        }

        // Solde dû
        $solde = $facture->getSoldeRestant();
        if ($solde > 0) {
            $tInner->addRow();
            $tInner->addCell($lw, $topNavy)->addText('Solde dû', $fTtc);
            $tInner->addCell($vw, $topNavy)->addText(number_format($solde, 2, ',', ' ') . ' €', $fTtc, $pR);
        }

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
