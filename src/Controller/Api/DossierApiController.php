<?php

namespace App\Controller\Api;

use App\Entity\Chronologie;
use App\Entity\Dossier;
use App\Enum\MatiereEnum;
use App\Enum\StatutDossierEnum;
use App\Enum\TypeConventionEnum;
use App\Enum\TypeEvenementEnum;
use App\Enum\TypePartieAdverseEnum;
use App\Repository\ChronologieRepository;
use App\Repository\ClientRepository;
use App\Repository\DocumentDossierRepository;
use App\Repository\DossierRepository;
use App\Repository\EcheanceRepository;
use App\Repository\PartieAdverseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dossiers', name: 'api_dossiers_')]
class DossierApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DossierRepository $dossierRepo,
        private readonly ClientRepository $clientRepo,
        private readonly EcheanceRepository $echeanceRepo,
        private readonly ChronologieRepository $chronologieRepo,
        private readonly PartieAdverseRepository $partieAdverseRepo,
        private readonly DocumentDossierRepository $documentRepo,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $statut = $request->query->get('statut');
        $matiere = $request->query->get('matiere');
        $clientId = $request->query->getInt('clientId') ?: null;
        $q = $request->query->get('q');

        $dossiers = $this->dossierRepo->findFiltered($statut, $matiere, $clientId, $q);
        return $this->json(['success' => true, 'data' => array_map(fn($d) => $d->toArray(), $dossiers)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['titre'])) {
            return $this->json(['success' => false, 'error' => 'Le titre est obligatoire'], 422);
        }
        if (empty($data['clientId'])) {
            return $this->json(['success' => false, 'error' => 'Le client est obligatoire'], 422);
        }

        $client = $this->clientRepo->find($data['clientId']);
        if (!$client) {
            return $this->json(['success' => false, 'error' => 'Client non trouvé'], 404);
        }

        $dossier = new Dossier();
        $dossier->setClient($client);
        $this->hydrateDossier($dossier, $data);

        $this->em->persist($dossier);

        // Événement de chronologie automatique
        $chrono = new Chronologie();
        $chrono->setDossier($dossier);
        $chrono->setType(TypeEvenementEnum::systeme);
        $chrono->setTitre('Ouverture du dossier');
        $chrono->setDescription('Dossier ouvert le ' . $dossier->getDateOuverture()->format('d/m/Y'));
        $chrono->setAuto(true);
        $this->em->persist($chrono);

        $this->em->flush();

        return $this->json(['success' => true, 'data' => $dossier->toArray()], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        return $this->json(['success' => true, 'data' => $dossier->toArray()]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['clientId'])) {
            $client = $this->clientRepo->find($data['clientId']);
            if ($client) $dossier->setClient($client);
        }
        $this->hydrateDossier($dossier, $data);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $dossier->toArray()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $this->em->remove($dossier);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/archiver', name: 'archiver', methods: ['POST'])]
    public function archiver(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $dossier->setStatut(StatutDossierEnum::Archive);
        $this->addChronologie($dossier, TypeEvenementEnum::systeme, 'Dossier archivé', 'Le dossier a été archivé');
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $dossier->toArray()]);
    }

    #[Route('/{id}/desarchiver', name: 'desarchiver', methods: ['POST'])]
    public function desarchiver(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $dossier->setStatut(StatutDossierEnum::En_cours);
        $this->addChronologie($dossier, TypeEvenementEnum::systeme, 'Dossier réactivé', 'Le dossier a été désarchivé');
        $this->em->flush();
        return $this->json(['success' => true, 'data' => $dossier->toArray()]);
    }

    #[Route('/{id}/chronologie', name: 'chronologie_list', methods: ['GET'])]
    public function chronologieList(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $events = $this->chronologieRepo->findByDossier($id);
        return $this->json(['success' => true, 'data' => array_map(fn($e) => $e->toArray(), $events)]);
    }

    #[Route('/{id}/chronologie', name: 'chronologie_add', methods: ['POST'])]
    public function chronologieAdd(int $id, Request $request): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $chrono = new Chronologie();
        $chrono->setDossier($dossier);
        $chrono->setTitre($data['titre'] ?? 'Événement');
        $chrono->setDescription($data['description'] ?? null);
        $chrono->setType(TypeEvenementEnum::tryFrom($data['type'] ?? '') ?? TypeEvenementEnum::note);
        if (!empty($data['date'])) {
            $chrono->setDate(new \DateTimeImmutable($data['date']));
        }

        $this->em->persist($chrono);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $chrono->toArray()], 201);
    }

    #[Route('/{id}/echeances', name: 'echeances', methods: ['GET'])]
    public function echeances(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $echeances = $this->echeanceRepo->findByDossier($id);
        return $this->json(['success' => true, 'data' => array_map(fn($e) => $e->toArray(), $echeances)]);
    }

    #[Route('/{id}/parties-adverses', name: 'parties_adverses', methods: ['GET'])]
    public function partiesAdverses(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $parties = $this->partieAdverseRepo->findByDossier($id);
        return $this->json(['success' => true, 'data' => array_map(fn($p) => $p->toArray(), $parties)]);
    }

    #[Route('/{id}/documents', name: 'documents', methods: ['GET'])]
    public function documents(int $id): JsonResponse
    {
        $dossier = $this->dossierRepo->find($id);
        if (!$dossier) {
            return $this->json(['success' => false, 'error' => 'Dossier non trouvé'], 404);
        }
        $docs = $this->documentRepo->findByDossier($id);
        return $this->json(['success' => true, 'data' => array_map(fn($d) => $d->toArray(), $docs)]);
    }

    private function hydrateDossier(Dossier $dossier, array $data): void
    {
        if (isset($data['titre'])) $dossier->setTitre($data['titre']);
        if (isset($data['couleur'])) $dossier->setCouleur($data['couleur']);
        if (isset($data['notes'])) $dossier->setNotes($data['notes'] ?: null);
        if (isset($data['statut'])) {
            $s = StatutDossierEnum::tryFrom($data['statut']);
            if ($s) $dossier->setStatut($s);
        }
        if (isset($data['matiere'])) {
            $m = MatiereEnum::tryFrom($data['matiere']);
            $dossier->setMatiere($m);
        }
        if (isset($data['dateOuverture'])) {
            $dossier->setDateOuverture(new \DateTimeImmutable($data['dateOuverture']));
        }
        if (array_key_exists('partieAdverseNom', $data)) $dossier->setPartieAdverseNom($data['partieAdverseNom'] ?: null);
        if (array_key_exists('partieAdversePrenom', $data)) $dossier->setPartieAdversePrenom($data['partieAdversePrenom'] ?: null);
        if (isset($data['partieAdverseType'])) {
            $t = TypePartieAdverseEnum::tryFrom($data['partieAdverseType']);
            if ($t) $dossier->setPartieAdverseType($t);
        }
        if (array_key_exists('partieAdverseEntreprise', $data)) $dossier->setPartieAdverseEntreprise($data['partieAdverseEntreprise'] ?: null);
        if (array_key_exists('conventionSousBranche', $data)) $dossier->setConventionSousBranche($data['conventionSousBranche'] ?: null);
        if (isset($data['conventionTypeHonoraire'])) {
            $c = TypeConventionEnum::tryFrom($data['conventionTypeHonoraire']);
            $dossier->setConventionTypeHonoraire($c);
        }
        if (array_key_exists('conventionMontantFixe', $data)) $dossier->setConventionMontantFixe($data['conventionMontantFixe'] !== null && $data['conventionMontantFixe'] !== '' ? (string)$data['conventionMontantFixe'] : null);
        if (array_key_exists('conventionPourcentageResultat', $data)) $dossier->setConventionPourcentageResultat($data['conventionPourcentageResultat'] !== null && $data['conventionPourcentageResultat'] !== '' ? (string)$data['conventionPourcentageResultat'] : null);
    }

    private function addChronologie(Dossier $dossier, TypeEvenementEnum $type, string $titre, string $description): void
    {
        $chrono = new Chronologie();
        $chrono->setDossier($dossier);
        $chrono->setType($type);
        $chrono->setTitre($titre);
        $chrono->setDescription($description);
        $chrono->setAuto(true);
        $this->em->persist($chrono);
    }
}
