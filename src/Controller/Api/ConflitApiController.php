<?php

namespace App\Controller\Api;

use App\Entity\VerificationConflit;
use App\Repository\ClientRepository;
use App\Repository\PartieAdverseRepository;
use App\Repository\VerificationConflitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/conflits', name: 'api_conflits_')]
class ConflitApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VerificationConflitRepository $verificationRepo,
        private readonly PartieAdverseRepository $partieRepo,
        private readonly ClientRepository $clientRepo,
    ) {}

    #[Route('/verifications', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $verifications = $this->verificationRepo->findRecentes(50);
        return $this->json(['success' => true, 'data' => array_map(fn($v) => $v->toArray(), $verifications)]);
    }

    #[Route('/verifications/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $verification = $this->verificationRepo->find($id);
        if (!$verification) {
            return $this->json(['success' => false, 'error' => 'Vérification non trouvée'], 404);
        }
        $this->em->remove($verification);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/verifier', name: 'verifier', methods: ['POST'])]
    public function verifier(Request $request): JsonResponse
    {
        $data        = json_decode($request->getContent(), true) ?? [];
        $nom         = trim($data['nom'] ?? '');
        $prenom      = trim($data['prenom'] ?? '');
        $denomination = trim($data['denomination'] ?? '');
        $contexte    = trim($data['contexte'] ?? '');
        $notes       = trim($data['notes'] ?? '');
        $clientId    = $data['clientId'] ?? null;

        $termNom = $nom ?: $denomination;
        $nomRecherche = trim("$prenom $nom") ?: $denomination;

        if (!$nomRecherche) {
            return $this->json(['success' => false, 'error' => 'Veuillez saisir au moins un nom ou une dénomination'], 422);
        }

        $conflits = [];
        $seen     = [];

        // ① Recherche dans les parties adverses
        $parties = $this->partieRepo->searchByNom($termNom, $prenom);
        foreach ($parties as $partie) {
            similar_text(mb_strtolower($termNom), mb_strtolower($partie->getNom() ?? ''), $pct);
            if ($pct >= 70) {
                $key = 'dossier_' . $partie->getDossier()?->getId();
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $conflits[] = [
                        'type'       => 'dossier',
                        'id'         => $partie->getDossier()?->getId(),
                        'nom'        => trim(($partie->getPrenom() ?? '') . ' ' . ($partie->getNom() ?? '')),
                        'similarite' => (int) round($pct),
                        'detail'     => sprintf('Partie adverse dans le dossier "%s"', $partie->getDossier()?->getTitre()),
                    ];
                }
            }
        }

        // ② Recherche dans les clients existants
        $clients = $this->clientRepo->search($termNom);
        foreach ($clients as $client) {
            $fieldCompare = mb_strtolower($client->getNom() ?? '');
            similar_text(mb_strtolower($termNom), $fieldCompare, $pct);
            if ($pct >= 70) {
                $key = 'client_' . $client->getId();
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $conflits[] = [
                        'type'       => 'client',
                        'id'         => $client->getId(),
                        'nom'        => $client->getNomComplet(),
                        'similarite' => (int) round($pct),
                        'detail'     => 'Ce nom correspond à un client existant du cabinet',
                    ];
                }
            }
        }

        // Tri par similarité décroissante
        usort($conflits, fn($a, $b) => $b['similarite'] <=> $a['similarite']);

        // Sauvegarde de l'historique
        $clientAssocie = $clientId ? $this->clientRepo->find((int) $clientId) : null;

        $verification = new VerificationConflit();
        $verification->setPartieAdverseNom($nomRecherche);
        $verification->setContexte($contexte ?: null);
        $verification->setClient($clientAssocie);
        $verification->setResultat(json_encode([
            'aConflits' => !empty($conflits),
            'conflits'  => $conflits,
            'notes'     => $notes,
        ]));
        $this->em->persist($verification);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'data'    => [
                'aConflits'      => !empty($conflits),
                'conflits'       => $conflits,
                'nombreConflits' => count($conflits),
            ],
        ]);
    }
}
