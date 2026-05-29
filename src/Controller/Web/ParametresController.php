<?php

namespace App\Controller\Web;

use App\Enum\StatutActeEnum;
use App\Enum\StatutDossierEnum;
use App\Enum\StatutFactureEnum;
use App\Repository\CabinetConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParametresController extends AbstractController
{
    #[Route('/parametres', name: 'parametres_index')]
    public function index(CabinetConfigRepository $repo): Response
    {
        $config = $repo->getConfig();

        $matieres = $config->getMatieres();

        $couleursStatut = $config->getCouleursStatut();
        $statuts = array_map(fn(StatutDossierEnum $s) => [
            'label'   => $s->label(),
            'couleur' => $couleursStatut[$s->value] ?? $s->couleurDefaut(),
            'defaut'  => $s->couleurDefaut(),
        ], StatutDossierEnum::cases());

        $couleursStatutActe = $config->getCouleursStatutActe();
        $statutsActe = array_map(fn(StatutActeEnum $s) => [
            'label'   => $s->label(),
            'couleur' => $couleursStatutActe[$s->value] ?? $s->couleurDefaut(),
            'defaut'  => $s->couleurDefaut(),
        ], StatutActeEnum::cases());

        $couleursStatutFacture = $config->getCouleursStatutFacture();
        $statutsFacture = array_map(fn(StatutFactureEnum $s) => [
            'label'   => $s->label(),
            'couleur' => $couleursStatutFacture[$s->value] ?? $s->couleurDefaut(),
            'defaut'  => $s->couleurDefaut(),
        ], StatutFactureEnum::cases());

        $defaultsConflit = ['Conflit' => '#F87171', 'OK' => '#34D399'];
        $storedConflit = $config->getCouleursConflit();
        $conflits = array_map(fn($key, $defaut) => [
            'label'   => $key,
            'couleur' => $storedConflit[$key] ?? $defaut,
            'defaut'  => $defaut,
        ], array_keys($defaultsConflit), $defaultsConflit);

        $niveauxLabels = [
            'expire'    => 'Expirée',
            'critique'  => 'Critique (0–3 j)',
            'urgent'    => 'Urgent (4–7 j)',
            'attention' => 'Attention (8–14 j)',
            'normal'    => 'Normal (15–30 j)',
            'lointain'  => 'Lointain (> 30 j)',
        ];
        $couleursEcheance = $config->getCouleursEcheance();
        $defautsEcheance  = \App\Entity\CabinetConfig::getEcheanceDefauts();
        $niveauxEcheance = array_map(fn($key, $label) => [
            'key'     => $key,
            'label'   => $label,
            'couleur' => $couleursEcheance[$key],
            'defaut'  => $defautsEcheance[$key],
        ], array_keys($niveauxLabels), $niveauxLabels);

        return $this->render('parametres/index.html.twig', [
            'matieres'        => $matieres,
            'statuts'         => $statuts,
            'statutsActe'     => $statutsActe,
            'statutsFacture'  => $statutsFacture,
            'conflits'        => $conflits,
            'niveauxEcheance' => $niveauxEcheance,
        ]);
    }
}
