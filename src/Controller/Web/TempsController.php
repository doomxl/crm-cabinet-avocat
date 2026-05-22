<?php

namespace App\Controller\Web;

use App\Repository\CabinetConfigRepository;
use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use App\Repository\SessionTempsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempsController extends AbstractController
{
    #[Route('/temps', name: 'temps_index')]
    public function index(
        SessionTempsRepository $sessionRepo,
        ClientRepository $clientRepo,
        DossierRepository $dossierRepo,
        CabinetConfigRepository $configRepo
    ): Response {
        $sessionEnCours = $sessionRepo->findSessionEnCours();
        $config = $configRepo->getConfig();

        return $this->render('temps/index.html.twig', [
            'sessionEnCours' => $sessionEnCours,
            'tauxHoraireDefaut' => (float)$config->getTauxHoraireDefaut(),
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'dossiers' => $dossierRepo->findEnCours(),
        ]);
    }
}
