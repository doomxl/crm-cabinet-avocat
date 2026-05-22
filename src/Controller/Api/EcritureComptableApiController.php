<?php

namespace App\Controller\Api;

use App\Entity\EcritureComptable;
use App\Enum\CategorieEcritureEnum;
use App\Enum\ModeReglementEnum;
use App\Enum\TypeEcritureEnum;
use App\Repository\EcritureComptableRepository;
use App\Repository\FactureRepository;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ecritures', name: 'api_ecritures_')]
class EcritureComptableApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EcritureComptableRepository $repo,
        private readonly FactureRepository $factureRepo,
        private readonly ExportService $exportService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        $categorie = $request->query->get('categorie');
        $annee = $request->query->getInt('annee') ?: null;
        $ecritures = $this->repo->findFiltered($type, $categorie, $annee);
        return $this->json(['success' => true, 'data' => array_map(fn($e) => $e->toArray(), $ecritures)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $ecriture = new EcritureComptable();
        $this->hydrateEcriture($ecriture, $data);
        $this->em->persist($ecriture);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $ecriture->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $ecriture = $this->repo->find($id);
        if (!$ecriture) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $ecriture->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ecriture = $this->repo->find($id);
        if (!$ecriture) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateEcriture($ecriture, $data);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $ecriture->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ecriture = $this->repo->find($id);
        if (!$ecriture) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $this->em->remove($ecriture);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/rapprochement', name: 'rapprochement', methods: ['POST'])]
    public function rapprochement(): JsonResponse
    {
        // Import automatique des factures payées en écritures comptables
        $factures = $this->factureRepo->findAll();
        $created = 0;
        foreach ($factures as $facture) {
            foreach ($facture->getPaiements() as $paiement) {
                // Vérifie si déjà importé
                $existing = $this->repo->findOneBy(['facture' => $facture]);
                if (!$existing) {
                    $ecriture = new EcritureComptable();
                    $ecriture->setDate($paiement->getDatePaiement());
                    $ecriture->setType(TypeEcritureEnum::recette);
                    $ecriture->setCategorie(CategorieEcritureEnum::AA);
                    $ecriture->setLibelle('Honoraires - ' . $facture->getNumero() . ' - ' . $facture->getClient()?->getNomComplet());
                    $ecriture->setMontant($paiement->getMontant());
                    $ecriture->setModeReglement($paiement->getModeReglement());
                    $ecriture->setReference($paiement->getReference());
                    $ecriture->setFacture($facture);
                    $this->em->persist($ecriture);
                    $created++;
                }
            }
        }
        $this->em->flush();
        return $this->json(['success' => true, 'data' => ['created' => $created]]);
    }

    #[Route('/totaux', name: 'totaux', methods: ['GET'])]
    public function totaux(Request $request): JsonResponse
    {
        $annee = $request->query->getInt('annee', (int)date('Y'));
        $parCategorie = $this->repo->getTotauxParCategorie($annee);
        $parMois = $this->repo->getTotauxParMois($annee);
        return $this->json(['success' => true, 'data' => ['parCategorie' => $parCategorie, 'parMois' => $parMois, 'annee' => $annee]]);
    }

    #[Route('/export-csv', name: 'export_csv', methods: ['GET'])]
    public function exportCsv(Request $request): Response
    {
        $annee = $request->query->getInt('annee', (int)date('Y'));
        $ecritures = $this->repo->findByAnnee($annee);
        $csv = $this->exportService->exporterJournalCsv($ecritures);
        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="journal-bnc-' . $annee . '.csv"',
        ]);
    }

    #[Route('/export-2035', name: 'export_2035', methods: ['GET'])]
    public function export2035(Request $request): Response
    {
        $annee = $request->query->getInt('annee', (int)date('Y'));
        $ecritures = $this->repo->findByAnnee($annee);
        $csv = $this->exportService->exporterRecap2035($ecritures);
        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="recap-2035-' . $annee . '.csv"',
        ]);
    }

    private function hydrateEcriture(EcritureComptable $e, array $data): void
    {
        if (!empty($data['date'])) $e->setDate(new \DateTimeImmutable($data['date']));
        if (isset($data['type'])) {
            $t = TypeEcritureEnum::tryFrom($data['type']);
            if ($t) $e->setType($t);
        }
        if (isset($data['categorie'])) {
            $c = CategorieEcritureEnum::tryFrom($data['categorie']);
            if ($c) $e->setCategorie($c);
        }
        if (isset($data['libelle'])) $e->setLibelle($data['libelle']);
        if (isset($data['montant'])) $e->setMontant((string)$data['montant']);
        if (isset($data['modeReglement'])) {
            $m = ModeReglementEnum::tryFrom($data['modeReglement']);
            $e->setModeReglement($m);
        }
        if (array_key_exists('reference', $data)) $e->setReference($data['reference'] ?: null);
        if (array_key_exists('notes', $data)) $e->setNotes($data['notes'] ?: null);
        if (!empty($data['factureId'])) {
            $f = $this->factureRepo->find($data['factureId']);
            if ($f) $e->setFacture($f);
        }
    }
}
