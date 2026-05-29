<?php

namespace App\Twig;

use App\Enum\StatutActeEnum;
use App\Enum\StatutDossierEnum;
use App\Enum\StatutFactureEnum;
use App\Repository\CabinetConfigRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private CabinetConfigRepository $cabinetConfigRepository,
        #[Autowire(env: 'APP_ENV')] private string $appEnv = 'dev',
    ) {}

    public function getGlobals(): array
    {
        $config = $this->cabinetConfigRepository->getConfig();
        $matieresList = $config->getMatieres();
        $couleursMatieres = [];
        foreach ($matieresList as $m) {
            $couleursMatieres[$m['label']] = $m['couleur'];
        }

        $storedStatuts = $config->getCouleursStatut();
        $couleursStatuts = [];
        foreach (StatutDossierEnum::cases() as $s) {
            $couleursStatuts[$s->value] = $storedStatuts[$s->value] ?? $s->couleurDefaut();
        }

        $storedStatutsActe = $config->getCouleursStatutActe();
        $couleursStatutsActe = [];
        foreach (StatutActeEnum::cases() as $s) {
            $couleursStatutsActe[$s->value] = $storedStatutsActe[$s->value] ?? $s->couleurDefaut();
        }

        $storedStatutsFacture = $config->getCouleursStatutFacture();
        $couleursStatutsFacture = [];
        foreach (StatutFactureEnum::cases() as $s) {
            $couleursStatutsFacture[$s->value] = $storedStatutsFacture[$s->value] ?? $s->couleurDefaut();
        }

        $defaultsConflit = ['Conflit' => '#F87171', 'OK' => '#34D399'];
        $storedConflit = $config->getCouleursConflit();
        $couleursConflit = [];
        foreach ($defaultsConflit as $key => $defaut) {
            $couleursConflit[$key] = $storedConflit[$key] ?? $defaut;
        }

        $gitVersion = $this->appEnv === 'prod'
            ? (trim((string) @shell_exec('git describe --tags --always 2>/dev/null')) ?: 'prod')
            : 'dev';

        return [
            'app_version'              => $gitVersion,
            'cabinet_config'           => $config,
            'matieres_list'            => $matieresList,
            'couleurs_matieres'        => $couleursMatieres,
            'couleurs_statuts'         => $couleursStatuts,
            'couleurs_statuts_acte'    => $couleursStatutsActe,
            'couleurs_statuts_facture' => $couleursStatutsFacture,
            'couleurs_conflits'        => $couleursConflit,
            'couleurs_echeances'       => $config->getCouleursEcheance(),
        ];
    }
}
