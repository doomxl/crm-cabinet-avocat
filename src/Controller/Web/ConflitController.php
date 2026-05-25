<?php

namespace App\Controller\Web;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConflitController extends AbstractController
{
    #[Route('/conflits', name: 'conflit_index')]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('conflits/index.html.twig', [
            'clients' => $clientRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }
}
