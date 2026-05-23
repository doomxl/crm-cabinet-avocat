<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParametresController extends AbstractController
{
    #[Route('/parametres', name: 'parametres_index')]
    public function index(): Response
    {
        return $this->render('parametres/index.html.twig');
    }
}
