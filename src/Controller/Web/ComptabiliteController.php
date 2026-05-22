<?php

namespace App\Controller\Web;

use App\Enum\CategorieEcritureEnum;
use App\Enum\ModeReglementEnum;
use App\Enum\TypeEcritureEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComptabiliteController extends AbstractController
{
    #[Route('/comptabilite', name: 'comptabilite_index')]
    public function index(): Response
    {
        return $this->render('comptabilite/index.html.twig', [
            'typesEcriture' => TypeEcritureEnum::cases(),
            'categories' => CategorieEcritureEnum::cases(),
            'modesReglement' => ModeReglementEnum::cases(),
            'anneeEnCours' => (int)date('Y'),
        ]);
    }
}
