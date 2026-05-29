<?php

namespace App\Service;

use App\Entity\CabinetConfig;
use App\Entity\Dossier;
use App\Entity\Echeance;
use App\Enum\StatutEcheanceEnum;
use App\Enum\TypeEcheanceEnum;
use App\Repository\CabinetConfigRepository;

class EcheanceService
{
    private array $couleurs;

    public function __construct(CabinetConfigRepository $configRepo)
    {
        $this->couleurs = $configRepo->getConfig()->getCouleursEcheance();
    }

    /**
     * Calcule le niveau d'urgence d'une échéance
     * @return array{niveau: string, couleur: string, jours: int, texte: string}
     */
    public function getUrgence(Echeance $echeance): array
    {
        $jours = $echeance->getJoursRestants();

        if ($jours < 0) {
            return ['niveau' => 'expire', 'couleur' => $this->couleurs['expire'], 'jours' => $jours, 'texte' => 'Expirée il y a ' . abs($jours) . ' j'];
        }
        if ($jours === 0) {
            return ['niveau' => 'critique', 'couleur' => $this->couleurs['critique'], 'jours' => 0, 'texte' => "Aujourd'hui"];
        }
        if ($jours <= 3) {
            return ['niveau' => 'critique', 'couleur' => $this->couleurs['critique'], 'jours' => $jours, 'texte' => 'Dans ' . $jours . ' j'];
        }
        if ($jours <= 7) {
            return ['niveau' => 'urgent', 'couleur' => $this->couleurs['urgent'], 'jours' => $jours, 'texte' => 'Dans ' . $jours . ' j'];
        }
        if ($jours <= 14) {
            return ['niveau' => 'attention', 'couleur' => $this->couleurs['attention'], 'jours' => $jours, 'texte' => 'Dans ' . $jours . ' j'];
        }
        if ($jours <= 30) {
            return ['niveau' => 'normal', 'couleur' => $this->couleurs['normal'], 'jours' => $jours, 'texte' => 'Dans ' . $jours . ' j'];
        }
        return ['niveau' => 'lointain', 'couleur' => $this->couleurs['lointain'], 'jours' => $jours, 'texte' => 'Dans ' . $jours . ' j'];
    }

    /**
     * Génère des échéances recommandées selon la matière du dossier
     * @return array<array{type: TypeEcheanceEnum, description: string, delaiJours: int}>
     */
    public function genererDepuisTemplate(Dossier $dossier): array
    {
        $matiere = $dossier->getMatiere();
        $dateRef = $dossier->getDateOuverture();
        $echeances = [];

        $templates = $this->getTemplatesParMatiere($matiere);

        foreach ($templates as $template) {
            $echeance = new Echeance();
            $echeance->setDossier($dossier);
            $echeance->setType($template['type']);
            $echeance->setDescription($template['description']);
            $echeance->setDate($dateRef->modify('+' . $template['delaiJours'] . ' days'));
            $echeance->setStatut(StatutEcheanceEnum::A_venir);
            $echeances[] = $echeance;
        }

        return $echeances;
    }

    private function getTemplatesParMatiere(?string $matiere): array
    {
        return match($matiere) {
            'Droit familial' => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Audience de tentative de conciliation', 'delaiJours' => 30],
                ['type' => TypeEcheanceEnum::Depot_pieces, 'description' => 'Dépôt des pièces au greffe', 'delaiJours' => 15],
                ['type' => TypeEcheanceEnum::Delai_conclusions, 'description' => 'Délai de conclusions', 'delaiJours' => 60],
            ],
            'Droit pénal' => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Audience correctionnelle', 'delaiJours' => 45],
                ['type' => TypeEcheanceEnum::Delai_recours, 'description' => 'Délai de recours contre le jugement', 'delaiJours' => 10],
                ['type' => TypeEcheanceEnum::Depot_pieces, 'description' => 'Dépôt des mémoires', 'delaiJours' => 30],
            ],
            'Droit des affaires' => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Audience commerciale', 'delaiJours' => 60],
                ['type' => TypeEcheanceEnum::Delai_conclusions, 'description' => 'Conclusions en défense', 'delaiJours' => 45],
                ['type' => TypeEcheanceEnum::Expertise, 'description' => 'Expertise comptable', 'delaiJours' => 90],
            ],
            'Droit immobilier' => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Audience devant le juge des loyers', 'delaiJours' => 30],
                ['type' => TypeEcheanceEnum::Delai_recours, 'description' => "Délai d'appel", 'delaiJours' => 30],
                ['type' => TypeEcheanceEnum::Depot_pieces, 'description' => 'Dépôt des pièces', 'delaiJours' => 15],
            ],
            'Droit social' => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Audience prud\'homale', 'delaiJours' => 45],
                ['type' => TypeEcheanceEnum::Delai_conclusions, 'description' => 'Conclusions récapitulatives', 'delaiJours' => 30],
            ],
            default => [
                ['type' => TypeEcheanceEnum::Audience, 'description' => 'Prochaine audience', 'delaiJours' => 30],
                ['type' => TypeEcheanceEnum::Delai_recours, 'description' => 'Délai de recours', 'delaiJours' => 30],
            ],
        };
    }
}
