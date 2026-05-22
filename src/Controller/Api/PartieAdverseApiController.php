<?php

namespace App\Controller\Api;

use App\Entity\PartieAdverse;
use App\Enum\TypePartieAdverseEnum;
use App\Repository\DossierRepository;
use App\Repository\PartieAdverseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/parties-adverses', name: 'api_parties_adverses_')]
class PartieAdverseApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartieAdverseRepository $repo,
        private readonly DossierRepository $dossierRepo,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $dossierId = $request->query->getInt('dossierId') ?: null;
        $parties = $dossierId ? $this->repo->findByDossier($dossierId) : $this->repo->findAll();
        return $this->json(['success' => true, 'data' => array_map(fn($p) => $p->toArray(), $parties)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        if (empty($data['dossierId'])) {
            return $this->json(['success' => false, 'error' => 'dossierId obligatoire'], 422);
        }
        $dossier = $this->dossierRepo->find($data['dossierId']);
        if (!$dossier) return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);

        $partie = new PartieAdverse();
        $partie->setDossier($dossier);
        $this->hydratePartie($partie, $data);
        $this->em->persist($partie);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $partie->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $partie = $this->repo->find($id);
        if (!$partie) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        return $this->json(['success' => true, 'data' => $partie->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $partie = $this->repo->find($id);
        if (!$partie) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $data = json_decode($request->getContent(), true) ?? [];
        $this->hydratePartie($partie, $data);
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $partie->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $partie = $this->repo->find($id);
        if (!$partie) return $this->json(['success' => false, 'error' => 'Non trouvé'], 404);
        $this->em->remove($partie);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    private function hydratePartie(PartieAdverse $partie, array $data): void
    {
        if (array_key_exists('nom', $data)) $partie->setNom($data['nom'] ?: null);
        if (array_key_exists('prenom', $data)) $partie->setPrenom($data['prenom'] ?: null);
        if (isset($data['typePartie'])) {
            $t = TypePartieAdverseEnum::tryFrom($data['typePartie']);
            if ($t) $partie->setTypePartie($t);
        }
        if (array_key_exists('entreprise', $data)) $partie->setEntreprise($data['entreprise'] ?: null);
        if (array_key_exists('notes', $data)) $partie->setNotes($data['notes'] ?: null);
        if (array_key_exists('siret', $data)) $partie->setSiret($data['siret'] ?: null);
        if (array_key_exists('siren', $data)) $partie->setSiren($data['siren'] ?: null);
        if (array_key_exists('rcs', $data)) $partie->setRcs($data['rcs'] ?: null);
        if (array_key_exists('formeJuridique', $data)) $partie->setFormeJuridique($data['formeJuridique'] ?: null);
        if (array_key_exists('capitalSocial', $data)) $partie->setCapitalSocial($data['capitalSocial'] !== null ? (string)$data['capitalSocial'] : null);
        if (array_key_exists('representantLegal', $data)) $partie->setRepresentantLegal($data['representantLegal'] ?: null);
        if (array_key_exists('qualiteRepresentant', $data)) $partie->setQualiteRepresentant($data['qualiteRepresentant'] ?: null);
        if (array_key_exists('adresse', $data)) $partie->setAdresse($data['adresse'] ?: null);
        if (array_key_exists('codePostal', $data)) $partie->setCodePostal($data['codePostal'] ?: null);
        if (array_key_exists('ville', $data)) $partie->setVille($data['ville'] ?: null);
        if (array_key_exists('avocatAdverse', $data)) $partie->setAvocatAdverse($data['avocatAdverse'] ?: null);
        if (array_key_exists('barreauAdverse', $data)) $partie->setBarreauAdverse($data['barreauAdverse'] ?: null);
    }
}
