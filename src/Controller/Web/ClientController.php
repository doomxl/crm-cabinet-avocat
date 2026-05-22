<?php

namespace App\Controller\Web;

use App\Enum\TypeClientEnum;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/clients', name: 'client_')]
class ClientController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('clients/index.html.twig', [
            'typesClient' => TypeClientEnum::cases(),
        ]);
    }

    #[Route('/nouveau', name: 'new')]
    public function new(): Response
    {
        return $this->render('clients/form.html.twig', [
            'client' => null,
            'typesClient' => TypeClientEnum::cases(),
            'mode' => 'creation',
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, ClientRepository $repo): Response
    {
        $client = $repo->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé');
        }
        return $this->render('clients/show.html.twig', [
            'client' => $client,
            'typesClient' => TypeClientEnum::cases(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, ClientRepository $repo): Response
    {
        $client = $repo->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé');
        }
        return $this->render('clients/form.html.twig', [
            'client' => $client,
            'typesClient' => TypeClientEnum::cases(),
            'mode' => 'edition',
        ]);
    }
}
