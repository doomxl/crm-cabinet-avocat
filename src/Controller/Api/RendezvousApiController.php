<?php

namespace App\Controller\Api;

use App\Entity\Rendezvous;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\RendezvousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/rendezvous', name: 'api_rendezvous_')]
class RendezvousApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RendezvousRepository $rdvRepo,
        private readonly ClientRepository $clientRepo,
        private readonly DossierRepository $dossierRepo,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if ($request->query->has('date')) {
            $date = new \DateTimeImmutable($request->query->get('date'));
            $rdvs = $this->rdvRepo->findByDate($date);
        } elseif ($request->query->has('semaine')) {
            $rdvs = $this->rdvRepo->findBySemaine($request->query->get('semaine'));
        } else {
            $rdvs = $this->rdvRepo->findAll();
        }
        return $this->json(['success' => true, 'data' => array_map(fn($r) => $r->toArray(), $rdvs)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $rdv = new Rendezvous();
        $this->hydrateRdv($rdv, $data);
        $this->em->persist($rdv);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $rdv->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $rdv = $this->rdvRepo->find($id);
        if (!$rdv) return $this->json(['success' => false, 'error' => 'Rendez-vous non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $rdv->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $rdv = $this->rdvRepo->find($id);
        if (!$rdv) return $this->json(['success' => false, 'error' => 'Rendez-vous non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateRdv($rdv, $data);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $rdv->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $rdv = $this->rdvRepo->find($id);
        if (!$rdv) return $this->json(['success' => false, 'error' => 'Rendez-vous non trouvé'], 404);
        $this->em->remove($rdv);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    private function hydrateRdv(Rendezvous $rdv, array $data): void
    {
        if (!empty($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
            $rdv->setClient($client);
        }
        if (!empty($data['dossierId'])) {
            $dossier = $this->dossierRepo->find($data['dossierId']);
            $rdv->setDossier($dossier);
        }
        if (!empty($data['date'])) $rdv->setDate(new \DateTimeImmutable($data['date']));
        if (!empty($data['heure'])) $rdv->setHeure(new \DateTime($data['heure']));
        if (isset($data['duree'])) $rdv->setDuree((int)$data['duree']);
        if (isset($data['type'])) $rdv->setType($data['type']);
        if (isset($data['lieu'])) $rdv->setLieu($data['lieu']);
        if (array_key_exists('lieuAutre', $data)) $rdv->setLieuAutre($data['lieuAutre'] ?: null);
        if (array_key_exists('notes', $data)) $rdv->setNotes($data['notes'] ?: null);
    }
}
