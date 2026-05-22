<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchApiController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $clientRepo,
        private readonly DossierRepository $dossierRepo,
    ) {}

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));

        if (strlen($q) < 2) {
            return $this->json(['success' => true, 'data' => ['clients' => [], 'dossiers' => []]]);
        }

        $clients  = $this->clientRepo->searchGlobal($q);
        $dossiers = $this->dossierRepo->searchGlobal($q);

        return $this->json([
            'success' => true,
            'data' => [
                'clients'  => array_map(fn($c) => $c->toArray(), $clients),
                'dossiers' => array_map(fn($d) => $d->toArray(), $dossiers),
            ],
        ]);
    }
}
