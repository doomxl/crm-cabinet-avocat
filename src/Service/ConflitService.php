<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Dossier;
use App\Entity\VerificationConflit;
use App\Repository\PartieAdverseRepository;
use App\Repository\VerificationConflitRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConflitService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PartieAdverseRepository $partieAdverseRepository,
        private readonly VerificationConflitRepository $verificationConflitRepository,
    ) {}

    public function verifierConflitClient(Client $client): array
    {
        $conflits = [];
        $nom = $client->getNom();
        $prenom = $client->getPrenom() ?? '';
        $intitule = $client->getIntitule() ?? '';

        // Chercher si le client est partie adverse dans un dossier
        $parties = $this->partieAdverseRepository->searchByNom($nom, $prenom);
        foreach ($parties as $partie) {
            if ($this->nomsSimilaires($partie->getNom() ?? '', $nom)
                && ($prenom === '' || $this->nomsSimilaires($partie->getPrenom() ?? '', $prenom))) {
                $conflits[] = [
                    'type' => 'client_vs_partie_adverse',
                    'partie' => $partie->toArray(),
                    'dossier' => $partie->getDossier()?->toArray(),
                    'message' => sprintf(
                        'Le client %s est partie adverse dans le dossier "%s"',
                        $client->getNomComplet(),
                        $partie->getDossier()?->getTitre()
                    ),
                ];
            }
        }

        if ($intitule) {
            $partiesEntreprise = $this->partieAdverseRepository->searchByNom($intitule);
            foreach ($partiesEntreprise as $partie) {
                if ($this->nomsSimilaires($partie->getEntreprise() ?? '', $intitule)) {
                    $conflits[] = [
                        'type' => 'entreprise_vs_partie_adverse',
                        'partie' => $partie->toArray(),
                        'dossier' => $partie->getDossier()?->toArray(),
                        'message' => sprintf(
                            'L\'entreprise "%s" est partie adverse dans le dossier "%s"',
                            $intitule,
                            $partie->getDossier()?->getTitre()
                        ),
                    ];
                }
            }
        }

        return $conflits;
    }

    public function verifierConflitDossier(Dossier $dossier): array
    {
        $conflits = [];
        $partieNom = $dossier->getPartieAdverseNom() ?? '';
        $partiePrenom = $dossier->getPartieAdversePrenom() ?? '';

        if (!$partieNom) {
            return [];
        }

        // Chercher si la partie adverse est un client existant
        $clientRepo = $this->entityManager->getRepository(Client::class);
        $clients = $clientRepo->search($partieNom);
        foreach ($clients as $client) {
            if ($this->nomsSimilaires($client->getNom(), $partieNom)) {
                $conflits[] = [
                    'type' => 'partie_adverse_est_client',
                    'client' => $client->toArray(),
                    'message' => sprintf(
                        'La partie adverse "%s" est un client du cabinet',
                        trim($partiePrenom . ' ' . $partieNom)
                    ),
                ];
            }
        }

        // Chercher si la partie adverse est partie dans un autre dossier (même camp)
        $autresParties = $this->partieAdverseRepository->searchByNom($partieNom, $partiePrenom);
        foreach ($autresParties as $partie) {
            if ($partie->getDossier()?->getId() !== $dossier->getId()) {
                $conflits[] = [
                    'type' => 'partie_adverse_dans_autre_dossier',
                    'partie' => $partie->toArray(),
                    'dossier' => $partie->getDossier()?->toArray(),
                    'message' => sprintf(
                        'La partie adverse "%s" apparaît aussi dans le dossier "%s"',
                        trim($partiePrenom . ' ' . $partieNom),
                        $partie->getDossier()?->getTitre()
                    ),
                ];
            }
        }

        return $conflits;
    }

    public function enregistrerVerification(?Client $client, ?Dossier $dossier, array $conflits): VerificationConflit
    {
        $verification = new VerificationConflit();
        $verification->setClient($client);
        $verification->setDossier($dossier);

        $nomRecherche = '';
        if ($client) {
            $nomRecherche = $client->getNomComplet();
        } elseif ($dossier && $dossier->getPartieAdverseNom()) {
            $nomRecherche = trim(($dossier->getPartieAdversePrenom() ?? '') . ' ' . $dossier->getPartieAdverseNom());
        }
        $verification->setPartieAdverseNom($nomRecherche);

        $resultatTexte = empty($conflits)
            ? 'Aucun conflit détecté'
            : implode("\n", array_column($conflits, 'message'));
        $verification->setResultat($resultatTexte);

        $this->entityManager->persist($verification);
        $this->entityManager->flush();

        return $verification;
    }

    private function nomsSimilaires(string $a, string $b): bool
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));
        if ($a === '' || $b === '') return false;
        similar_text($a, $b, $percent);
        return $percent >= 80;
    }
}
