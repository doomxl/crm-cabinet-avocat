<?php

namespace App\Controller\Web;

use App\Enum\MatiereEnum;
use App\Enum\StatutActeEnum;
use App\Enum\StatutDossierEnum;
use App\Enum\StatutFactureEnum;
use App\Repository\CabinetConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme', name: 'theme_index')]
    public function index(CabinetConfigRepository $repo): Response
    {
        $config = $repo->getConfig();
        $couleursMatiere = $config->getCouleursMatiere();

        $matieres = array_map(fn(MatiereEnum $m) => [
            'label'   => $m->label(),
            'couleur' => $couleursMatiere[$m->value] ?? $m->couleur(),
            'defaut'  => $m->couleur(),
        ], MatiereEnum::cases());

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

        return $this->redirectToRoute('parametres_index', [], 301);
    }
}
