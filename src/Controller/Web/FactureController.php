<?php

namespace App\Controller\Web;

use App\Enum\ModeReglementEnum;
use App\Enum\StatutFactureEnum;
use App\Enum\TypeDocumentEnum;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/honoraires', name: 'facture_')]
class FactureController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('honoraires/index.html.twig', [
            'statuts' => StatutFactureEnum::cases(),
            'typesDocument' => TypeDocumentEnum::cases(),
        ]);
    }

    #[Route('/nouvelle', name: 'new')]
    public function new(ClientRepository $clientRepo, DossierRepository $dossierRepo): Response
    {
        return $this->render('honoraires/form.html.twig', [
            'facture' => null,
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'typesDocument' => TypeDocumentEnum::cases(),
            'modesReglement' => ModeReglementEnum::cases(),
            'statuts' => StatutFactureEnum::cases(),
            'mode' => 'creation',
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, FactureRepository $repo): Response
    {
        $facture = $repo->find($id);
        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }
        return $this->render('honoraires/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, FactureRepository $repo, ClientRepository $clientRepo): Response
    {
        $facture = $repo->find($id);
        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }
        return $this->render('honoraires/form.html.twig', [
            'facture' => $facture,
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'typesDocument' => TypeDocumentEnum::cases(),
            'modesReglement' => ModeReglementEnum::cases(),
            'statuts' => StatutFactureEnum::cases(),
            'mode' => 'edition',
        ]);
    }
}
