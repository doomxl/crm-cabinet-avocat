<?php

namespace App\Controller\Web;

use App\Repository\CabinetConfigRepository;
use App\Repository\EcheanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard_index')]
    public function index(EcheanceRepository $echeanceRepo, CabinetConfigRepository $configRepo): Response
    {
        $config = $configRepo->getConfig();
        $nbEcheancesUrgentes = $echeanceRepo->countUrgentes(7);

        return $this->render('dashboard/index.html.twig', [
            'config' => $config,
            'nbEcheancesUrgentes' => $nbEcheancesUrgentes,
        ]);
    }
}
