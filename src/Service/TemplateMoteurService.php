<?php

namespace App\Service;

use App\Entity\CabinetConfig;
use App\Entity\Client;
use App\Entity\Dossier;
use App\Entity\PartieAdverse;

class TemplateMoteurService
{
    /**
     * Résout un template avec :
     *   {{variable}}                              — substitution simple
     *   {{variable | filtre}}                     — substitution avec filtre
     *   {{#si condition}}...{{/si}}               — bloc conditionnel
     *   {{#si condition}}...{{#sinon}}...{{/si}}  — conditionnel avec branche fausse
     *   {{#pour item in liste}}...{{/pour}}       — boucle sur une liste
     *
     * Conditions supportées :
     *   variable                               — test de présence (truthy)
     *   variable == "valeur"                   — égalité (string)
     *   variable != "valeur"                   — inégalité
     *   variable > 1000                        — comparaison numérique
     *   variable >= 1000 ET autre_variable     — ET logique
     *   cond1 OU cond2                         — OU logique
     *   NON variable                           — NON logique
     *
     * Filtres disponibles :
     *   majuscule | minuscule | capitalise | monnaie | date_longue
     */
    public function resoudre(string $template, array $variables): string
    {
        // 1. Boucles : {{#pour item in liste}}...{{/pour}}
        $template = preg_replace_callback(
            '/\{\{#pour\s+(\w+)\s+in\s+(\w+(?:\.\w+)*)\}\}(.*?)\{\{\/pour\}\}/s',
            function (array $matches) use ($variables): string {
                $nomItem  = $matches[1];
                $nomListe = $matches[2];
                $contenu  = $matches[3];

                $liste = $this->getVariable($nomListe, $variables);
                if (!is_array($liste) || empty($liste)) {
                    return '';
                }

                $resultat = '';
                foreach ($liste as $item) {
                    $contexte = $variables;
                    $contexte[$nomItem] = $item;
                    $resultat .= $this->resoudre($contenu, $contexte);
                }
                return $resultat;
            },
            $template
        );

        // 2. Blocs conditionnels avec #sinon optionnel
        $template = preg_replace_callback(
            '/\{\{#si\s+([^}]+?)\s*\}\}(.*?)(?:\{\{#sinon\}\}(.*?))?\{\{\/si\}\}/s',
            function (array $matches) use ($variables): string {
                $contenuVrai = $matches[2];
                $contenuFaux = $matches[3] ?? '';

                return $this->evaluerCondition($matches[1], $variables)
                    ? $this->resoudre($contenuVrai, $variables)
                    : $this->resoudre($contenuFaux, $variables);
            },
            $template
        );

        // 3. Variables avec filtre : {{variable | filtre}}
        $template = preg_replace_callback(
            '/\{\{(\w+(?:\.\w+)*)\s*\|\s*(\w+)\}\}/',
            function (array $matches) use ($variables): string {
                $valeur = (string)($this->getVariable($matches[1], $variables) ?? '');
                return $this->appliquerFiltre($valeur, $matches[2]);
            },
            $template
        );

        // 4. Variables simples : {{variable}} ou {{objet.champ}}
        $template = preg_replace_callback(
            '/\{\{(\w+(?:\.\w+)*)\}\}/',
            function (array $matches) use ($variables): string {
                $valeur = $this->getVariable($matches[1], $variables);
                return is_string($valeur) || is_numeric($valeur) ? (string)$valeur : '';
            },
            $template
        );

        return $template;
    }

    /**
     * Convertit un texte Markdown minimal en HTML.
     * Supporte : # titres, **gras**, *italique*, - liste, 1. liste, ---
     */
    public function markdownVersHtml(string $texte): string
    {
        $lignes   = explode("\n", $texte);
        $html     = '';
        $enUl     = false;
        $enOl     = false;

        $fermerListes = function () use (&$html, &$enUl, &$enOl): void {
            if ($enUl) { $html .= "</ul>\n"; $enUl = false; }
            if ($enOl) { $html .= "</ol>\n"; $enOl = false; }
        };

        foreach ($lignes as $ligne) {
            $trim = rtrim($ligne);

            if ($trim === '') {
                $fermerListes();
                $html .= "<br>\n";
                continue;
            }

            if (preg_match('/^-{3,}$/', $trim)) {
                $fermerListes();
                $html .= "<hr>\n";
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.+)$/', $trim, $m)) {
                $fermerListes();
                $n = strlen($m[1]);
                $html .= "<h{$n}>" . $this->inlineMarkdown($m[2]) . "</h{$n}>\n";
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trim, $m)) {
                if ($enOl) { $html .= "</ol>\n"; $enOl = false; }
                if (!$enUl) { $html .= "<ul>\n"; $enUl = true; }
                $html .= '<li>' . $this->inlineMarkdown($m[1]) . "</li>\n";
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trim, $m)) {
                if ($enUl) { $html .= "</ul>\n"; $enUl = false; }
                if (!$enOl) { $html .= "<ol>\n"; $enOl = true; }
                $html .= '<li>' . $this->inlineMarkdown($m[1]) . "</li>\n";
                continue;
            }

            $fermerListes();
            $html .= '<p>' . $this->inlineMarkdown($trim) . "</p>\n";
        }

        $fermerListes();
        return $html;
    }

    private function inlineMarkdown(string $texte): string
    {
        $texte = htmlspecialchars($texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $texte = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $texte);
        $texte = preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $texte);
        return $texte;
    }

    /**
     * Construit le contexte de variables à partir des entités métier.
     *
     * @param PartieAdverse[] $parties
     */
    public function construireVariables(Dossier $dossier, array $parties, CabinetConfig $config): array
    {
        $client       = $dossier->getClient();
        $today        = new \DateTimeImmutable();
        $premierPartie = $parties[0] ?? null;

        return [
            // --- Cabinet ---
            'cabinet_nom'         => $config->getNom(),
            'cabinet_adresse'     => $config->getAdresse() ?? '',
            'cabinet_code_postal' => $config->getCodePostal() ?? '',
            'cabinet_ville'       => $config->getVille() ?? '',
            'cabinet_telephone'   => $config->getTelephone() ?? '',
            'cabinet_email'       => $config->getEmail() ?? '',
            'cabinet_siret'       => $config->getSiret() ?? '',
            'avocat_nom'          => $config->getAvocatNom() ?? '',
            'avocat_barreau'      => $config->getAvocatBarreau() ?? '',
            'avocat_numero'       => $config->getAvocatNumero() ?? '',

            // --- Client ---
            'client_nom'          => $client?->getNom() ?? '',
            'client_prenom'       => $client?->getPrenom() ?? '',
            'client_nom_complet'  => $client?->getNomComplet() ?? '',
            'client_type'         => $client?->getTypeClient()?->value ?? '',
            'client_civilite'     => $this->getCivilite($client),
            'client_email'        => $client?->getEmail() ?? '',
            'client_telephone'    => $client?->getTelephone() ?? '',
            'client_adresse'      => $client?->getAdresse() ?? '',
            'client_ville'        => $client?->getVille() ?? '',
            'client_code_postal'  => $client?->getCodePostal() ?? '',
            'client_siret'        => $client?->getSiret() ?? '',
            'client_intitule'     => $client?->getIntitule() ?? '',

            // --- Dossier ---
            'dossier_id'             => (string)($dossier->getId() ?? ''),
            'dossier_titre'          => $dossier->getTitre(),
            'dossier_matiere'        => $dossier->getMatiere() ?? '',
            'dossier_date_ouverture' => $dossier->getDateOuverture()->format('d/m/Y'),
            'dossier_statut'         => $dossier->getStatut()->value,
            'dossier_notes'          => $dossier->getNotes() ?? '',

            // --- Partie adverse principale (rétrocompatibilité) ---
            'partie_adverse_nom'         => $premierPartie?->getNom() ?? $dossier->getPartieAdverseNom() ?? '',
            'partie_adverse_prenom'      => $premierPartie?->getPrenom() ?? $dossier->getPartieAdversePrenom() ?? '',
            'partie_adverse_nom_complet' => $premierPartie?->getNomComplet() ?? trim(($dossier->getPartieAdversePrenom() ?? '') . ' ' . ($dossier->getPartieAdverseNom() ?? '')),
            'partie_adverse_entreprise'  => $premierPartie?->getEntreprise() ?? $dossier->getPartieAdverseEntreprise() ?? '',
            'partie_adverse_avocat'      => $premierPartie?->getAvocatAdverse() ?? '',
            'partie_adverse_barreau'     => $premierPartie?->getBarreauAdverse() ?? '',

            // --- Toutes les parties adverses (pour les boucles {{#pour partie in parties_adverses}}) ---
            'parties_adverses' => array_map(fn(PartieAdverse $p) => [
                'nom'              => $p->getNom() ?? '',
                'prenom'           => $p->getPrenom() ?? '',
                'nom_complet'      => $p->getNomComplet(),
                'type'             => $p->getTypePartie()->value,
                'entreprise'       => $p->getEntreprise() ?? '',
                'adresse'          => $p->getAdresse() ?? '',
                'ville'            => $p->getVille() ?? '',
                'siret'            => $p->getSiret() ?? '',
                'representant_legal'     => $p->getRepresentantLegal() ?? '',
                'qualite_representant'   => $p->getQualiteRepresentant() ?? '',
                'avocat'           => $p->getAvocatAdverse() ?? '',
                'barreau'          => $p->getBarreauAdverse() ?? '',
            ], $parties),

            // --- Dates ---
            'date_jour'     => $today->format('d'),
            'date_mois'     => $today->format('m'),
            'date_annee'    => $today->format('Y'),
            'date_complete' => $today->format('d/m/Y'),
            'date_longue'   => $this->dateLongue($today),
        ];
    }

    // -------------------------------------------------------------------------
    // Évaluation des conditions
    // -------------------------------------------------------------------------

    private function evaluerCondition(string $condition, array $variables): bool
    {
        $condition = trim($condition);

        // OU (priorité plus faible — évalué en premier pour respecter la précédence)
        if (preg_match('/^(.+?)\s+OU\s+(.+)$/su', $condition, $m)) {
            return $this->evaluerCondition($m[1], $variables)
                || $this->evaluerCondition($m[2], $variables);
        }

        // ET (priorité plus haute)
        if (preg_match('/^(.+?)\s+ET\s+(.+)$/su', $condition, $m)) {
            return $this->evaluerCondition($m[1], $variables)
                && $this->evaluerCondition($m[2], $variables);
        }

        // NON
        if (preg_match('/^NON\s+(.+)$/su', $condition, $m)) {
            return !$this->evaluerCondition($m[1], $variables);
        }

        // Comparaisons : variable OP valeur
        if (preg_match('/^(\w+(?:\.\w+)*)\s*(==|!=|>=|<=|>|<)\s*(.+)$/', $condition, $m)) {
            $gauche    = (string)($this->getVariable(trim($m[1]), $variables) ?? '');
            $operateur = $m[2];
            $droite    = trim($m[3], '"\'');

            return match ($operateur) {
                '==' => $gauche === $droite,
                '!=' => $gauche !== $droite,
                '>'  => (float)$gauche > (float)$droite,
                '<'  => (float)$gauche < (float)$droite,
                '>=' => (float)$gauche >= (float)$droite,
                '<=' => (float)$gauche <= (float)$droite,
            };
        }

        // Truthiness simple
        $valeur = $this->getVariable($condition, $variables);
        return $valeur !== null && $valeur !== '' && $valeur !== false && $valeur !== '0';
    }

    // -------------------------------------------------------------------------
    // Accès aux variables (supporte les clés imbriquées : "objet.champ")
    // -------------------------------------------------------------------------

    private function getVariable(string $key, array $variables): mixed
    {
        $parts = explode('.', $key);
        $valeur = $variables;
        foreach ($parts as $part) {
            if (is_array($valeur) && array_key_exists($part, $valeur)) {
                $valeur = $valeur[$part];
            } else {
                return null;
            }
        }
        return $valeur;
    }

    // -------------------------------------------------------------------------
    // Filtres
    // -------------------------------------------------------------------------

    private function appliquerFiltre(string $valeur, string $filtre): string
    {
        return match ($filtre) {
            'majuscule'   => mb_strtoupper($valeur),
            'minuscule'   => mb_strtolower($valeur),
            'capitalise'  => mb_convert_case($valeur, MB_CASE_TITLE, 'UTF-8'),
            'monnaie'     => number_format((float)$valeur, 2, ',', ' ') . ' €',
            'date_longue' => $this->dateStringLongue($valeur),
            default       => $valeur,
        };
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getCivilite(?Client $client): string
    {
        if (!$client) {
            return '';
        }
        return $client->getCivilite()?->value ?? '';
    }

    private function dateLongue(\DateTimeImmutable $date): string
    {
        $mois = [
            1 => 'janvier', 2 => 'février',  3 => 'mars',      4 => 'avril',
            5 => 'mai',     6 => 'juin',     7 => 'juillet',   8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        return $date->format('j') . ' ' . $mois[(int)$date->format('n')] . ' ' . $date->format('Y');
    }

    private function dateStringLongue(string $dateStr): string
    {
        try {
            return $this->dateLongue(new \DateTimeImmutable($dateStr));
        } catch (\Exception) {
            return $dateStr;
        }
    }
}
