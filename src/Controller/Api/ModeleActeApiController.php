<?php

namespace App\Controller\Api;

use App\Entity\ModeleActe;
use App\Repository\ModeleActeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/modeles-actes', name: 'api_modeles_actes_')]
class ModeleActeApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ModeleActeRepository $repo,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $categorie = $request->query->get('categorie');
        $modeles = $categorie ? $this->repo->findByCategorie($categorie) : $this->repo->findBy([], ['categorie' => 'ASC', 'nom' => 'ASC']);
        return $this->json(['success' => true, 'data' => array_map(fn($m) => $m->toArray(), $modeles)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        if (empty($data['nom'])) return $this->json(['success' => false, 'error' => 'Nom obligatoire'], 422);
        $modele = new ModeleActe();
        $this->hydrateModele($modele, $data);
        $this->em->persist($modele);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $modele->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $modele = $this->repo->find($id);
        if (!$modele) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $modele->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $modele = $this->repo->find($id);
        if (!$modele) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydrateModele($modele, $data);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $modele->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $modele = $this->repo->find($id);
        if (!$modele) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $this->em->remove($modele);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    private function hydrateModele(ModeleActe $modele, array $data): void
    {
        if (isset($data['nom'])) $modele->setNom($data['nom']);
        if (array_key_exists('categorie', $data)) $modele->setCategorie($data['categorie'] ?: null);
        if (array_key_exists('contenu', $data)) $modele->setContenu($data['contenu'] ?: null);
    }
}
