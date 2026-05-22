<?php

namespace App\Controller\Api;

use App\Entity\SessionTemps;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\SessionTempsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sessions', name: 'api_sessions_')]
class SessionTempsApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SessionTempsRepository $repo,
        private readonly DossierRepository $dossierRepo,
        private readonly ClientRepository $clientRepo,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $sessions = $this->repo->findBy([], ['debut' => 'DESC']);
        $enCours = $this->repo->findSessionEnCours();
        return $this->json(['success' => true, 'data' => array_map(fn($s) => $s->toArray(), $sessions), 'sessionEnCours' => $enCours?->toArray()]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $session = new SessionTemps();
        $this->hydrateSession($session, $data);
        $this->em->persist($session);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $session->toArray()], 201);
    }

    #[Route('/par-dossier', name: 'par_dossier', methods: ['GET'])]
    public function parDossier(Request $request): JsonResponse
    {
        $dossierId = $request->query->getInt('dossierId');
        if (!$dossierId) return $this->json(['success' => false, 'error' => 'dossierId requis'], 422);
        $sessions = $this->repo->findParDossier($dossierId);
        return $this->json(['success' => true, 'data' => array_map(fn($s) => $s->toArray(), $sessions)]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $session = $this->repo->find($id);
        if (!$session) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $session->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $session = $this->repo->find($id);
        if (!$session) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateSession($session, $data);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $session->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $session = $this->repo->find($id);
        if (!$session) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $this->em->remove($session);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/stop', name: 'stop', methods: ['POST'])]
    public function stop(int $id): JsonResponse
    {
        $session = $this->repo->find($id);
        if (!$session) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $session->setFin(new \DateTimeImmutable());
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $session->toArray()]);
    }

    private function hydrateSession(SessionTemps $session, array $data): void
    {
        if (!empty($data['dossierId'])) {
            $dossier = $this->dossierRepo->find($data['dossierId']);
            $session->setDossier($dossier);
        }
        if (!empty($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
            $session->setClient($client);
        }
        if (isset($data['tauxHoraire'])) $session->setTauxHoraire((string)$data['tauxHoraire']);
        if (!empty($data['debut'])) $session->setDebut(new \DateTimeImmutable($data['debut']));
        if (array_key_exists('fin', $data)) {
            $session->setFin($data['fin'] ? new \DateTimeImmutable($data['fin']) : null);
        }
        if (array_key_exists('description', $data)) $session->setDescription($data['description'] ?: null);
    }
}
