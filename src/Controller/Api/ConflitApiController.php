<?php

namespace App\Controller\Api;

use App\Repository\PartieAdverseRepository;
use App\Repository\VerificationConflitRepository;
use App\Service\ConflitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/conflits', name: 'api_conflits_')]
class ConflitApiController extends AbstractController
{
    public function __construct(
        private readonly VerificationConflitRepository $verificationRepo,
        private readonly PartieAdverseRepository $partieRepo,
        private readonly ConflitService $conflitService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $verifications = $this->verificationRepo->findRecentes(50);
        return $this->json(['success' => true, 'data' => array_map(fn($v) => $v->toArray(), $verifications)]);
    }

    #[Route('/verifier', name: 'verifier', methods: ['POST'])]
    public function verifier(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');

        if (!$nom) {
            return $this->json(['success' => false, 'error' => 'Le nom est obligatoire'], 422);
        }

        // Recherche dans les parties adverses
        $parties = $this->partieRepo->searchByNom($nom, $prenom);
        $conflits = [];

        foreach ($parties as $partie) {
            $conflits[] = [
                'type' => 'partie_adverse_trouvee',
                'partie' => $partie->toArray(),
                'dossier' => $partie->getDossier()?->toArray(),
                'message' => sprintf(
                    '"%s %s" est partie adverse dans le dossier "%s"',
                    $prenom,
                    $nom,
                    $partie->getDossier()?->getTitre()
                ),
            ];
        }

        $verification = new \App\Entity\VerificationConflit();
        $verification->setPartieAdverseNom(trim($prenom . ' ' . $nom));
        $verification->setResultat(empty($conflits) ? 'Aucun conflit détecté' : implode("\n", array_column($conflits, 'message')));

        // On ne persiste pas ici pour les vérifications manuelles rapides sans client/dossier associé
        // Si on veut conserver, utiliser le ConflitService

        return $this->json([
            'success' => true,
            'data' => [
                'nom' => $nom,
                'prenom' => $prenom,
                'conflits' => $conflits,
                'conflit_detecte' => !empty($conflits),
                'message' => empty($conflits) ? 'Aucun conflit détecté' : count($conflits) . ' conflit(s) détecté(s)',
            ]
        ]);
    }
}
