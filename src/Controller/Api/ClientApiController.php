<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Enum\CiviliteEnum;
use App\Enum\TypeClientEnum;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\FactureRepository;
use App\Service\ConflitService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/clients', name: 'api_clients_')]
class ClientApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientRepository $clientRepo,
        private readonly DossierRepository $dossierRepo,
        private readonly FactureRepository $factureRepo,
        private readonly ConflitService $conflitService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
        $q = $request->query->get('q', '');

        $result = $this->clientRepo->findPaginated($page, $limit, $q ?: null);
        $data = array_map(fn(Client $c) => $c->toArray(), $result['items']);

        return $this->json(['success' => true, 'data' => $data, 'meta' => [
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
        ]]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        if (!isset($data['nom']) || trim($data['nom']) === '') {
            return $this->json(['success' => false, 'error' => 'Le nom est obligatoire'], 422);
        }

        $client = new Client();
        $this->hydrateClient($client, $data);

        if (empty($client->getCodeClient())) {
            $client->setCodeClient('C-' . date('Y') . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT));
        }

        $this->em->persist($client);
        $this->em->flush();

        // Vérification conflits automatique
        $conflits = $this->conflitService->verifierConflitClient($client);
        if (!empty($conflits)) {
            $this->conflitService->enregistrerVerification($client, null, $conflits);
        }

        return $this->json(['success' => true, 'data' => $client->toArray(), 'conflits' => $conflits], 201);
    }

    #[Route('/export-csv', name: 'export_csv', methods: ['GET'])]
    public function exportCsv(): Response
    {
        $clients = $this->clientRepo->findAll();

        $headers = ['id', 'codeClient', 'typeClient', 'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville', 'codePostal', 'intitule', 'siret', 'typeSociete', 'codeAPE', 'cph', 'createdAt', 'updatedAt'];

        $rows = [];
        $rows[] = implode(';', $headers);
        foreach ($clients as $client) {
            $data = $client->toArray();
            $row = array_map(fn($key) => '"' . str_replace('"', '""', (string)($data[$key] ?? '')) . '"', $headers);
            $rows[] = implode(';', $row);
        }

        return new Response(implode("\n", $rows), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clients-' . date('Y-m-d') . '.csv"',
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }
        return $this->json(['success' => true, 'data' => $client->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateClient($client, $data);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $client->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }
        $this->em->remove($client);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/dossiers', name: 'dossiers', methods: ['GET'])]
    public function dossiers(int $id): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }
        $dossiers = $this->dossierRepo->findByClient($id);
        return $this->json(['success' => true, 'data' => array_map(fn($d) => $d->toArray(), $dossiers)]);
    }

    #[Route('/{id}/factures', name: 'factures', methods: ['GET'])]
    public function factures(int $id): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }
        $factures = $this->factureRepo->findByClient($id);
        return $this->json(['success' => true, 'data' => array_map(fn($f) => $f->toArray(), $factures)]);
    }

    #[Route('/{id}/verifier-conflit', name: 'verifier_conflit', methods: ['POST'])]
    public function verifierConflit(int $id): JsonResponse
    {
        $client = $this->clientRepo->find($id);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }
        $conflits = $this->conflitService->verifierConflitClient($client);
        $verification = $this->conflitService->enregistrerVerification($client, null, $conflits);
        return $this->json(['success' => true, 'data' => ['conflits' => $conflits, 'verification' => $verification->toArray()]]);
    }

    private function hydrateClient(Client $client, array $data): void
    {
        if (isset($data['nom'])) $client->setNom($data['nom']);
        if (array_key_exists('prenom', $data)) $client->setPrenom($data['prenom'] ?: null);
        if (isset($data['typeClient'])) {
            $type = TypeClientEnum::tryFrom($data['typeClient']);
            if ($type) $client->setTypeClient($type);
        }
        if (array_key_exists('email', $data)) $client->setEmail($data['email'] ?: null);
        if (array_key_exists('telephone', $data)) $client->setTelephone($data['telephone'] ?: null);
        if (array_key_exists('adresse', $data)) $client->setAdresse($data['adresse'] ?: null);
        if (array_key_exists('ville', $data)) $client->setVille($data['ville'] ?: null);
        if (array_key_exists('codePostal', $data)) $client->setCodePostal($data['codePostal'] ?: null);
        if (array_key_exists('intitule', $data)) $client->setIntitule($data['intitule'] ?: null);
        if (array_key_exists('siret', $data)) $client->setSiret($data['siret'] ?: null);
        if (array_key_exists('typeSociete', $data)) $client->setTypeSociete($data['typeSociete'] ?: null);
        if (array_key_exists('codeAPE', $data)) $client->setCodeAPE($data['codeAPE'] ?: null);
        if (array_key_exists('cph', $data)) $client->setCph($data['cph'] ?: null);
        if (array_key_exists('codeClient', $data)) $client->setCodeClient($data['codeClient'] ?: null);
        if (array_key_exists('civilite', $data)) {
            $civ = CiviliteEnum::tryFrom($data['civilite'] ?? '');
            $client->setCivilite($civ);
        }
    }
}
