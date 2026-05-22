<?php

namespace App\Controller\Web;

use App\Repository\ActeGenereRepository;
use App\Repository\ModeleActeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/actes', name: 'acte_')]
class ActeController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ModeleActeRepository $modeleRepo, ActeGenereRepository $acteRepo): Response
    {
        return $this->render('actes/index.html.twig', [
            'modeles' => $modeleRepo->findBy([], ['categorie' => 'ASC', 'nom' => 'ASC']),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, ActeGenereRepository $repo): Response
    {
        $acte = $repo->find($id);
        if (!$acte) {
            throw $this->createNotFoundException('Acte non trouvé');
        }
        return $this->render('actes/show.html.twig', [
            'acte' => $acte,
        ]);
    }
}
