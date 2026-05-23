<?php

namespace App\Controller\Api;

use App\Repository\CabinetConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/config', name: 'api_config_')]
class CabinetConfigApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CabinetConfigRepository $repo,
    ) {}

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $config = $this->repo->getConfig();
        return $this->json(['success' => true, 'data' => $config->toArray()]);
    }

    #[Route('', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request): JsonResponse
    {
        $config = $this->repo->getConfig();
        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['nom'])) $config->setNom($data['nom']);
        if (array_key_exists('adresse', $data)) $config->setAdresse($data['adresse'] ?: null);
        if (array_key_exists('codePostal', $data)) $config->setCodePostal($data['codePostal'] ?: null);
        if (array_key_exists('ville', $data)) $config->setVille($data['ville'] ?: null);
        if (array_key_exists('telephone', $data)) $config->setTelephone($data['telephone'] ?: null);
        if (array_key_exists('email', $data)) $config->setEmail($data['email'] ?: null);
        if (array_key_exists('siret', $data)) $config->setSiret($data['siret'] ?: null);
        if (array_key_exists('couleursMatiere', $data)) $config->setCouleursMatiere($data['couleursMatiere']);
        if (isset($data['tauxHoraireDefaut'])) $config->setTauxHoraireDefaut((string)$data['tauxHoraireDefaut']);
        if (array_key_exists('avocatNom', $data)) $config->setAvocatNom($data['avocatNom'] ?: null);
        if (array_key_exists('avocatBarreau', $data)) $config->setAvocatBarreau($data['avocatBarreau'] ?: null);
        if (array_key_exists('avocatNumero', $data)) $config->setAvocatNumero($data['avocatNumero'] ?: null);

        $this->em->flush();
        return $this->json(['success' => true, 'data' => $config->toArray()]);
    }
}
