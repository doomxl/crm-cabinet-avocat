<?php

namespace App\Service;

use App\Entity\ActeGenere;
use App\Entity\CabinetConfig;
use App\Entity\Facture;
use App\Service\TemplateMoteurService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TemplateMoteurService $templateService,
    ) {}

    private function projectRoot(): string
    {
        return str_replace('\\', '/', dirname(__DIR__, 2));
    }

    private function createDompdf(): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', $this->projectRoot());
        return new Dompdf($options);
    }

    public function genererFacturePdf(Facture $facture, CabinetConfig $config): string
    {
        $fontPath = $this->projectRoot() . '/public/fonts';

        $html = $this->twig->render('pdf/facture.html.twig', [
            'facture'      => $facture,
            'config'       => $config,
            'lignes'       => $facture->getLignes()->toArray(),
            'paiements'    => $facture->getPaiements()->toArray(),
            'montantPaye'  => $facture->getMontantPaye(),
            'soldeRestant' => $facture->getSoldeRestant(),
            'fontPath'     => $fontPath,
        ]);

        $dompdf = $this->createDompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function genererActePdf(ActeGenere $acte, CabinetConfig $config): string
    {
        $contenuResolu = $this->templateService->markdownVersHtml($acte->getContenu() ?? '');

        $fontPath = $this->projectRoot() . '/public/fonts';

        $html = $this->twig->render('pdf/acte.html.twig', [
            'acte'          => $acte,
            'config'        => $config,
            'contenuResolu' => $contenuResolu,
            'fontPath'      => $fontPath,
        ]);

        $dompdf = $this->createDompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function genererHtml(string $template, array $vars): string
    {
        return $this->twig->render($template, $vars);
    }
}
