<?php

namespace App\Controller\Web;

use App\Repository\ClientRepository;
use App\Repository\DossierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgendaController extends AbstractController
{
    #[Route('/agenda', name: 'agenda_index')]
    public function index(ClientRepository $clientRepo, DossierRepository $dossierRepo): Response
    {
        return $this->render('agenda/index.html.twig', [
            'clients' => $clientRepo->findBy([], ['nom' => 'ASC']),
            'typesRdv' => ['Consultation', 'Audience', 'Réunion', 'Expertises', 'Autre'],
        ]);
    }
}
