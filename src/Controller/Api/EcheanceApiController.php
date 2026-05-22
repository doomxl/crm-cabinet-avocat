<?php

namespace App\Controller\Api;

use App\Entity\Echeance;
use App\Enum\StatutEcheanceEnum;
use App\Enum\TypeEcheanceEnum;
use App\Repository\DossierRepository;
use App\Repository\EcheanceRepository;
use App\Service\EcheanceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/echeances', name: 'api_echeances_')]
class EcheanceApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EcheanceRepository $echeanceRepo,
        private readonly DossierRepository $dossierRepo,
        private readonly EcheanceService $echeanceService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $statut = $request->query->get('statut');
        $dossierId = $request->query->getInt('dossierId') ?: null;
        $urgenceJours = $request->query->has('urgence') ? $request->query->getInt('urgence', 7) : null;

        $echeances = $this->echeanceRepo->findFiltered($statut, $dossierId, $urgenceJours);
        $data = array_map(function (Echeance $e) {
            $arr = $e->toArray();
            $arr['urgence'] = $this->echeanceService->getUrgence($e);
            return $arr;
        }, $echeances);

        return $this->json(['success' => true, 'data' => $data]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['dossierId'])) {
            return $this->json(['success' => false, 'error' => 'Le dossier est obligatoire'], 422);
        }
        $dossier = $this->dossierRepo->find($data['dossierId']);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }

        $echeance = new Echeance();
        $echeance->setDossier($dossier);
        $this->hydrateEcheance($echeance, $data);

        $this->em->persist($echeance);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $echeance->toArray()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $echeance = $this->echeanceRepo->find($id);
        if (!$echeance) return $this->json(['success' => false, 'error' => 'Échéance non trouvée'], 404);

        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateEcheance($echeance, $data);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $echeance->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $echeance = $this->echeanceRepo->find($id);
        if (!$echeance) return $this->json(['success' => false, 'error' => 'Échéance non trouvée'], 404);
        $this->em->remove($echeance);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/statut', name: 'statut', methods: ['PATCH'])]
    public function statut(int $id, Request $request): JsonResponse
    {
        $echeance = $this->echeanceRepo->find($id);
        if (!$echeance) return $this->json(['success' => false, 'error' => 'Échéance non trouvée'], 404);

        $data = json_decode($request->getContent(), true) ?? [];
        if (empty($data['statut'])) {
            return $this->json(['success' => false, 'error' => 'Le statut est obligatoire'], 422);
        }
        $statut = StatutEcheanceEnum::tryFrom($data['statut']);
        if (!$statut) {
            return $this->json(['success' => false, 'error' => 'Statut invalide'], 422);
        }
        $echeance->setStatut($statut);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $echeance->toArray()]);
    }

    private function hydrateEcheance(Echeance $echeance, array $data): void
    {
        if (isset($data['type'])) {
            $t = TypeEcheanceEnum::tryFrom($data['type']);
            if ($t) $echeance->setType($t);
        }
        if (!empty($data['date'])) $echeance->setDate(new \DateTimeImmutable($data['date']));
        if (array_key_exists('description', $data)) $echeance->setDescription($data['description'] ?: null);
        if (isset($data['statut'])) {
            $s = StatutEcheanceEnum::tryFrom($data['statut']);
            if ($s) $echeance->setStatut($s);
        }
    }
}
