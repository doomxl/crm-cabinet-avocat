<?php

namespace App\Service;

use App\Entity\CabinetConfig;
use App\Entity\Dossier;
use App\Entity\PartieAdverse;

class TemplateMoteurService
{
    /**
     * Résout un template avec les variables {{var}} et {{#si condition}}...{{/si}}
     */
    public function resoudre(string $template, array $variables): string
    {
        // Résoudre les blocs conditionnels {{#si var}}...{{/si}}
        $template = preg_replace_callback(
            '/\{\{#si\s+(\w+)\}\}(.*?)\{\{\/si\}\}/s',
            function (array $matches) use ($variables): string {
                $condition = $matches[1];
                $contenu = $matches[2];
                $valeur = $variables[$condition] ?? null;
                return ($valeur && $valeur !== '' && $valeur !== false && $valeur !== null)
                    ? $this->resoudre($contenu, $variables)
                    : '';
            },
            $template
        );

        // Résoudre les variables simples {{var}}
        $template = preg_replace_callback(
            '/\{\{(\w+(?:\.\w+)*)\}\}/',
            function (array $matches) use ($variables): string {
                $key = $matches[1];
                // Support nested keys like "client.nom"
                $parts = explode('.', $key);
                $value = $variables;
                foreach ($parts as $part) {
                    if (is_array($value) && isset($value[$part])) {
                        $value = $value[$part];
                    } else {
                        return ''; // Variable non trouvée
                    }
                }
                return is_string($value) || is_numeric($value) ? (string)$value : '';
            },
            $template
        );

        return $template;
    }

    /**
     * Construit le tableau de variables pour la résolution d'un template d'acte
     */
    public function construireVariables(Dossier $dossier, ?PartieAdverse $partie, CabinetConfig $config): array
    {
        $client = $dossier->getClient();
        $today = new \DateTimeImmutable();

        return [
            // Cabinet
            'cabinet_nom' => $config->getNom(),
            'cabinet_adresse' => $config->getAdresse() ?? '',
            'cabinet_code_postal' => $config->getCodePostal() ?? '',
            'cabinet_ville' => $config->getVille() ?? '',
            'cabinet_telephone' => $config->getTelephone() ?? '',
            'cabinet_email' => $config->getEmail() ?? '',
            'cabinet_siret' => $config->getSiret() ?? '',

            // Client
            'client_nom' => $client?->getNom() ?? '',
            'client_prenom' => $client?->getPrenom() ?? '',
            'client_nom_complet' => $client?->getNomComplet() ?? '',
            'client_email' => $client?->getEmail() ?? '',
            'client_telephone' => $client?->getTelephone() ?? '',
            'client_adresse' => $client?->getAdresse() ?? '',
            'client_ville' => $client?->getVille() ?? '',
            'client_code_postal' => $client?->getCodePostal() ?? '',
            'client_siret' => $client?->getSiret() ?? '',
            'client_intitule' => $client?->getIntitule() ?? '',

            // Dossier
            'dossier_id' => (string)($dossier->getId() ?? ''),
            'dossier_titre' => $dossier->getTitre(),
            'dossier_matiere' => $dossier->getMatiere()?->value ?? '',
            'dossier_date_ouverture' => $dossier->getDateOuverture()->format('d/m/Y'),
            'dossier_statut' => $dossier->getStatut()->value,

            // Partie adverse principale
            'partie_adverse_nom' => $partie?->getNom() ?? $dossier->getPartieAdverseNom() ?? '',
            'partie_adverse_prenom' => $partie?->getPrenom() ?? $dossier->getPartieAdversePrenom() ?? '',
            'partie_adverse_nom_complet' => $partie?->getNomComplet() ?? trim(($dossier->getPartieAdversePrenom() ?? '') . ' ' . ($dossier->getPartieAdverseNom() ?? '')),
            'partie_adverse_entreprise' => $partie?->getEntreprise() ?? $dossier->getPartieAdverseEntreprise() ?? '',
            'partie_adverse_avocat' => $partie?->getAvocatAdverse() ?? '',
            'partie_adverse_barreau' => $partie?->getBarreauAdverse() ?? '',

            // Date du jour
            'date_jour' => $today->format('d'),
            'date_mois' => $today->format('m'),
            'date_annee' => $today->format('Y'),
            'date_complete' => $today->format('d/m/Y'),
            'date_longue' => $this->dateLongue($today),
        ];
    }

    private function dateLongue(\DateTimeImmutable $date): string
    {
        $mois = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];
        return $date->format('j') . ' ' . $mois[(int)$date->format('n')] . ' ' . $date->format('Y');
    }
}
