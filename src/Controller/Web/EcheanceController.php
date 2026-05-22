<?php

namespace App\Controller\Web;

use App\Enum\StatutEcheanceEnum;
use App\Enum\TypeEcheanceEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EcheanceController extends AbstractController
{
    #[Route('/echeances', name: 'echeance_index')]
    public function index(): Response
    {
        return $this->render('echeances/index.html.twig', [
            'typesEcheance' => TypeEcheanceEnum::cases(),
            'statuts' => StatutEcheanceEnum::cases(),
        ]);
    }
}
