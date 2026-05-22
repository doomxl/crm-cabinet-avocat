<?php

namespace App\Controller\Api;

use App\Entity\ActeGenere;
use App\Repository\ActeGenereRepository;
use App\Repository\CabinetConfigRepository;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\ModeleActeRepository;
use App\Repository\PartieAdverseRepository;
use App\Service\PdfService;
use App\Service\TemplateMoteurService;
use App\Service\WordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/actes-generes', name: 'api_actes_generes_')]
class ActeGenereApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActeGenereRepository $repo,
        private readonly ModeleActeRepository $modeleRepo,
        private readonly DossierRepository $dossierRepo,
        private readonly ClientRepository $clientRepo,
        private readonly PartieAdverseRepository $partieRepo,
        private readonly CabinetConfigRepository $configRepo,
        private readonly TemplateMoteurService $templateService,
        private readonly PdfService $pdfService,
        private readonly WordService $wordService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $dossierId = $request->query->getInt('dossierId') ?: null;
        $actes = $dossierId ? $this->repo->findByDossier($dossierId) : $this->repo->findBy([], ['dateGeneration' => 'DESC']);
        return $this->json(['success' => true, 'data' => array_map(fn($a) => $a->toArray(), $actes)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $modele = null;
        if (!empty($data['modeleId'])) {
            $modele = $this->modeleRepo->find($data['modeleId']);
        }

        $dossier = null;
        if (!empty($data['dossierId'])) {
            $dossier = $this->dossierRepo->find($data['dossierId']);
        }

        $client = null;
        if (!empty($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
        } elseif ($dossier) {
            $client = $dossier->getClient();
        }

        // Résolution du template
        $contenu = $data['contenu'] ?? ($modele?->getContenu() ?? '');
        if ($dossier && $modele?->getContenu()) {
            $config = $this->configRepo->getConfig();
            $parties = $this->partieRepo->findByDossier($dossier->getId());
            $premierPartie = $parties[0] ?? null;
            $variables = $this->templateService->construireVariables($dossier, $premierPartie, $config);
            $contenu = $this->templateService->resoudre($modele->getContenu(), $variables);
        }

        $acte = new ActeGenere();
        $acte->setModele($modele);
        $acte->setDossier($dossier);
        $acte->setClient($client);
        $acte->setNom($data['nom'] ?? ($modele?->getNom() ?? 'Acte sans titre'));
        $acte->setContenu($contenu);

        $this->em->persist($acte);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $acte->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $acte = $this->repo->find($id);
        if (!$acte) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $acte->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $acte = $this->repo->find($id);
        if (!$acte) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['nom'])) $acte->setNom($data['nom']);
        if (isset($data['contenu'])) $acte->setContenu($data['contenu']);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $acte->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $acte = $this->repo->find($id);
        if (!$acte) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $this->em->remove($acte);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(int $id): Response
    {
        $acte = $this->repo->find($id);
        if (!$acte) return new Response('Non trouvé', 404);
        $config = $this->configRepo->getConfig();
        $pdf = $this->pdfService->genererActePdf($acte, $config);
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="acte-' . $acte->getId() . '.pdf"',
        ]);
    }

    #[Route('/{id}/docx', name: 'docx', methods: ['GET'])]
    public function docx(int $id): Response
    {
        $acte = $this->repo->find($id);
        if (!$acte) return new Response('Non trouvé', 404);
        $config = $this->configRepo->getConfig();
        $docx = $this->wordService->genererActeDocx($acte, $config);
        return new Response($docx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="acte-' . $acte->getId() . '.docx"',
        ]);
    }
}
