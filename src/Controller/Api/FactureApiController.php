<?php

namespace App\Controller\Api;

use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Entity\Paiement;
use App\Enum\ModeReglementEnum;
use App\Enum\StatutFactureEnum;
use App\Enum\TypeDocumentEnum;
use App\Repository\CabinetConfigRepository;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\FactureRepository;
use App\Repository\LigneFactureRepository;
use App\Repository\PaiementRepository;
use App\Service\FactureService;
use App\Service\PdfService;
use App\Service\WordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/factures', name: 'api_factures_')]
class FactureApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FactureRepository $factureRepo,
        private readonly ClientRepository $clientRepo,
        private readonly DossierRepository $dossierRepo,
        private readonly LigneFactureRepository $ligneRepo,
        private readonly PaiementRepository $paiementRepo,
        private readonly CabinetConfigRepository $configRepo,
        private readonly FactureService $factureService,
        private readonly PdfService $pdfService,
        private readonly WordService $wordService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $statut = $request->query->get('statut');
        $clientId = $request->query->getInt('clientId') ?: null;
        $dossierId = $request->query->getInt('dossierId') ?: null;
        $factures = $this->factureRepo->findFiltered($statut, $clientId, $dossierId);
        return $this->json(['success' => true, 'data' => array_map(fn($f) => $f->toArray(), $factures)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $facture = new Facture();
        $facture->setNumero($this->factureService->genererNumero());

        if (!empty($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
            if (!$client) return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
            $facture->setClient($client);
        }
        if (!empty($data['dossierId'])) {
            $dossier = $this->dossierRepo->find($data['dossierId']);
            if ($dossier) $facture->setDossier($dossier);
        }

        $this->hydrateFacture($facture, $data);
        $this->em->persist($facture);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $facture->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);

        $data = $facture->toArray();
        $data['lignes'] = array_map(fn($l) => $l->toArray(), $facture->getLignes()->toArray());
        $data['paiements'] = array_map(fn($p) => $p->toArray(), $facture->getPaiements()->toArray());

        return $this->json(['success' => true, 'data' => $data]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);

        $data = json_decode($request->getContent(), true) ?? [];
        if (!empty($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
            if ($client) $facture->setClient($client);
        }
        $this->hydrateFacture($facture, $data);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $facture->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);
        $this->em->remove($facture);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/lignes', name: 'ligne_add', methods: ['POST'])]
    public function ligneAdd(int $id, Request $request): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);

        $data = json_decode($request->getContent(), true) ?? [];
        $ligne = new LigneFacture();
        $ligne->setFacture($facture);
        $ligne->setDescription($data['description'] ?? '');
        $ligne->setQuantite((string)($data['quantite'] ?? 1));
        $ligne->setPrixUnitaire((string)($data['prixUnitaire'] ?? 0));
        $ligne->setUnite($data['unite'] ?? 'forfait');
        $ligne->setPosition($data['position'] ?? $facture->getLignes()->count());

        $this->em->persist($ligne);
        $this->factureService->recalculerMontantHt($facture);
        $this->factureService->mettreAJourStatut($facture);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $ligne->toArray()], 201);
    }

    #[Route('/{id}/lignes/{ligneId}', name: 'ligne_delete', methods: ['DELETE'])]
    public function ligneDelete(int $id, int $ligneId): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);
        $ligne = $this->ligneRepo->find($ligneId);
        if (!$ligne) return $this->json(['success' => false, 'error' => 'Ligne non trouvée'], 404);

        $this->em->remove($ligne);
        $this->factureService->recalculerMontantHt($facture);
        $this->factureService->mettreAJourStatut($facture);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/paiements', name: 'paiement_add', methods: ['POST'])]
    public function paiementAdd(int $id, Request $request): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);

        $data = json_decode($request->getContent(), true) ?? [];
        $paiement = new Paiement();
        $paiement->setFacture($facture);
        $paiement->setMontant((string)($data['montant'] ?? 0));
        if (!empty($data['datePaiement'])) {
            $paiement->setDatePaiement(new \DateTimeImmutable($data['datePaiement']));
        }
        if (!empty($data['modeReglement'])) {
            $mode = ModeReglementEnum::tryFrom($data['modeReglement']);
            if ($mode) $paiement->setModeReglement($mode);
        }
        $paiement->setReference($data['reference'] ?? null);
        $paiement->setNotes($data['notes'] ?? null);

        $this->em->persist($paiement);
        $this->factureService->mettreAJourStatut($facture);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $paiement->toArray()], 201);
    }

    #[Route('/{id}/paiements/{paiementId}', name: 'paiement_delete', methods: ['DELETE'])]
    public function paiementDelete(int $id, int $paiementId): JsonResponse
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return $this->json(['success' => false, 'error' => 'Facture non trouvée'], 404);
        $paiement = $this->paiementRepo->find($paiementId);
        if (!$paiement) return $this->json(['success' => false, 'error' => 'Paiement non trouvé'], 404);

        $this->em->remove($paiement);
        $this->factureService->mettreAJourStatut($facture);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(int $id): Response
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return new Response('Facture non trouvée', 404);

        $config = $this->configRepo->getConfig();
        $pdf = $this->pdfService->genererFacturePdf($facture, $config);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $facture->getNumero() . '.pdf"',
        ]);
    }

    #[Route('/{id}/docx', name: 'docx', methods: ['GET'])]
    public function docx(int $id): Response
    {
        $facture = $this->factureRepo->find($id);
        if (!$facture) return new Response('Facture non trouvée', 404);

        $config = $this->configRepo->getConfig();
        $docx = $this->wordService->genererFactureDocx($facture, $config);

        return new Response($docx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $facture->getNumero() . '.docx"',
        ]);
    }

    private function hydrateFacture(Facture $facture, array $data): void
    {
        if (isset($data['typeDocument'])) {
            $t = TypeDocumentEnum::tryFrom($data['typeDocument']);
            if ($t) $facture->setTypeDocument($t);
        }
        if (isset($data['montantHt'])) $facture->setMontantHt((string)$data['montantHt']);
        if (isset($data['tauxTva'])) $facture->setTauxTva((string)$data['tauxTva']);
        if (isset($data['provision'])) $facture->setProvision((string)$data['provision']);
        if (!empty($data['dateEmission'])) $facture->setDateEmission(new \DateTimeImmutable($data['dateEmission']));
        if (isset($data['statut'])) {
            $s = StatutFactureEnum::tryFrom($data['statut']);
            if ($s) $facture->setStatut($s);
        }
        if (isset($data['modeReglement'])) {
            $m = ModeReglementEnum::tryFrom($data['modeReglement']);
            if ($m) $facture->setModeReglement($m);
        }
        if (!empty($data['dossierId'])) {
            $dossier = $this->dossierRepo->find($data['dossierId']);
            if ($dossier) $facture->setDossier($dossier);
        }
    }
}
