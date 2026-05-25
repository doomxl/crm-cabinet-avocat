<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\EcheanceRepository;
use App\Repository\FactureRepository;
use App\Repository\RendezvousRepository;
use App\Service\EcheanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardApiController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $clientRepo,
        private readonly DossierRepository $dossierRepo,
        private readonly EcheanceRepository $echeanceRepo,
        private readonly FactureRepository $factureRepo,
        private readonly RendezvousRepository $rdvRepo,
        private readonly EcheanceService $echeanceService,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $today = new \DateTime();

        // KPIs
        $totalClients = count($this->clientRepo->findAll());
        $dossiersEnCours = $this->dossierRepo->findEnCours();
        $echeancesUrgentes = $this->echeanceRepo->findUrgentes(7);
        $caMoisCourant = $this->factureRepo->getMontantMensuel($today);
        $factures = $this->factureRepo->findImpayees();
        $rdvsAVenir = $this->rdvRepo->findToday();

        // Répartition par matière
        $parMatiere = $this->dossierRepo->countByMatiere();
        $statsByMatiere = [];
        foreach ($parMatiere as $row) {
            $statsByMatiere[] = [
                'matiere' => $row['matiere'],
                'total' => (int)$row['total'],
            ];
        }

        // Montant total des impayés
        $totalImpaye = 0.0;
        foreach ($factures as $facture) {
            $totalImpaye += $facture->getSoldeRestant();
        }

        // Alertes
        $alertes = [];
        if ($totalImpaye > 0) {
            $alertes[] = [
                'type' => 'impaye',
                'couleur' => '#EF4444',
                'message' => count($factures) . ' facture(s) impayée(s) - ' . number_format($totalImpaye, 2, ',', ' ') . ' € restant dû',
            ];
        }
        if (count($echeancesUrgentes) > 0) {
            $alertes[] = [
                'type' => 'echeance',
                'couleur' => '#F97316',
                'message' => count($echeancesUrgentes) . ' échéance(s) dans les 7 prochains jours',
            ];
        }

        return $this->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'totalClients' => $totalClients,
                    'dossiersEnCours' => count($dossiersEnCours),
                    'echeancesUrgentes' => count($echeancesUrgentes),
                    'caMoisCourant' => round($caMoisCourant, 2),
                    'totalImpaye' => round($totalImpaye, 2),
                ],
                'alertes' => $alertes,
                'echeancesUrgentes' => array_map(function ($e) {
                    $arr = $e->toArray();
                    $arr['urgence'] = $this->echeanceService->getUrgence($e);
                    return $arr;
                }, array_slice($echeancesUrgentes, 0, 10)),
                'rdvsAVenir' => array_map(fn($r) => $r->toArray(), $rdvsAVenir),
                'statsByMatiere' => $statsByMatiere,
                'factures' => array_map(fn($f) => $f->toArray(), array_slice($factures, 0, 5)),
            ]
        ]);
    }
}
