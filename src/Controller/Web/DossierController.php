<?php

namespace App\Controller\Web;

use App\Enum\StatutDossierEnum;
use App\Enum\TypeConventionEnum;
use App\Repository\CabinetConfigRepository;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dossiers', name: 'dossier_')]
class DossierController extends AbstractController
{
    public function __construct(private readonly CabinetConfigRepository $configRepo) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('dossiers/index.html.twig', [
            'matieres' => $this->configRepo->getConfig()->getMatieres(),
            'statuts' => StatutDossierEnum::cases(),
        ]);
    }

    #[Route('/nouveau', name: 'new')]
    public function new(ClientRepository $clientRepo): Response
    {
        return $this->render('dossiers/form.html.twig', [
            'dossier' => null,
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'matieres' => $this->configRepo->getConfig()->getMatieres(),
            'statuts' => StatutDossierEnum::cases(),
            'typesConvention' => TypeConventionEnum::cases(),
            'mode' => 'creation',
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, DossierRepository $repo): Response
    {
        $dossier = $repo->find($id);
        if (!$dossier) {
            throw $this->createNotFoundException('Dossier non trouvé');
        }
        return $this->render('dossiers/show.html.twig', [
            'dossier' => $dossier,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, DossierRepository $repo, ClientRepository $clientRepo): Response
    {
        $dossier = $repo->find($id);
        if (!$dossier) {
            throw $this->createNotFoundException('Dossier non trouvé');
        }
        return $this->render('dossiers/form.html.twig', [
            'dossier' => $dossier,
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'matieres' => $this->configRepo->getConfig()->getMatieres(),
            'statuts' => StatutDossierEnum::cases(),
            'typesConvention' => TypeConventionEnum::cases(),
            'mode' => 'edition',
        ]);
    }
}
