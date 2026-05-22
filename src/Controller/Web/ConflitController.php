<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConflitController extends AbstractController
{
    #[Route('/conflits', name: 'conflit_index')]
    public function index(): Response
    {
        return $this->render('conflits/index.html.twig');
    }
}
