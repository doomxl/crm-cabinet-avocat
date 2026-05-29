<?php

namespace App\Controller\Api;

use App\Repository\CabinetConfigRepository;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/config', name: 'api_config_')]
class CabinetConfigApiController extends AbstractController
{
    private const LOGO_MAX_SIZE  = 2 * 1024 * 1024; // 2 Mo
    private const LOGO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CabinetConfigRepository $repo,
        private readonly DossierRepository $dossierRepo,
        #[Autowire('%kernel.project_dir%/public/uploads/logo')] private readonly string $logoDir,
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
        if (array_key_exists('matieres', $data)) {
            $newMatieres = $data['matieres'];
            $config->setMatieres($newMatieres);
            $validLabels = array_column($newMatieres, 'label');
            $fallback = in_array('Autres', $validLabels) ? 'Autres' : null;
            $this->dossierRepo->updateObsoleteMatieres($validLabels, $fallback);
        }
        if (array_key_exists('couleursStatut', $data)) $config->setCouleursStatut($data['couleursStatut']);
        if (array_key_exists('couleursStatutActe', $data)) $config->setCouleursStatutActe($data['couleursStatutActe']);
        if (array_key_exists('couleursStatutFacture', $data)) $config->setCouleursStatutFacture($data['couleursStatutFacture']);
        if (array_key_exists('couleursConflit', $data)) $config->setCouleursConflit($data['couleursConflit']);
        if (array_key_exists('couleursEcheance', $data)) $config->setCouleursEcheance($data['couleursEcheance']);
        if (isset($data['tauxHoraireDefaut'])) $config->setTauxHoraireDefaut((string)$data['tauxHoraireDefaut']);
        if (array_key_exists('avocatNom', $data)) $config->setAvocatNom($data['avocatNom'] ?: null);
        if (array_key_exists('avocatBarreau', $data)) $config->setAvocatBarreau($data['avocatBarreau'] ?: null);
        if (array_key_exists('avocatNumero', $data)) $config->setAvocatNumero($data['avocatNumero'] ?: null);

        $this->em->flush();
        return $this->json(['success' => true, 'data' => $config->toArray()]);
    }

    #[Route('/logo', name: 'upload_logo', methods: ['POST'])]
    public function uploadLogo(Request $request): JsonResponse
    {
        $file = $request->files->get('logo');

        if (!$file) {
            $maxPost = ini_get('post_max_size');
            $maxUp   = ini_get('upload_max_filesize');
            return $this->json(['success' => false, 'error' => "Aucun fichier reçu (post_max_size={$maxPost}, upload_max_filesize={$maxUp})"], 422);
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'Fichier dépasse upload_max_filesize (' . ini_get('upload_max_filesize') . ')',
                UPLOAD_ERR_FORM_SIZE  => 'Fichier dépasse MAX_FILE_SIZE du formulaire',
                UPLOAD_ERR_PARTIAL    => 'Fichier partiellement uploadé',
                UPLOAD_ERR_NO_FILE    => 'Aucun fichier envoyé',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque',
            ];
            return $this->json(['success' => false, 'error' => $errors[$file->getError()] ?? 'Erreur upload #' . $file->getError()], 422);
        }

        if ($file->getSize() > self::LOGO_MAX_SIZE) {
            return $this->json(['success' => false, 'error' => 'Fichier trop volumineux (max 2 Mo)'], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, self::LOGO_EXTENSIONS, true)) {
            return $this->json(['success' => false, 'error' => 'Extension non autorisée. Formats acceptés : ' . implode(', ', self::LOGO_EXTENSIONS)], 422);
        }

        $filename = 'logo_' . uniqid() . '.' . $ext;

        if (!is_dir($this->logoDir)) {
            if (!mkdir($this->logoDir, 0777, true)) {
                return $this->json(['success' => false, 'error' => 'Impossible de créer le dossier : ' . $this->logoDir], 500);
            }
        }

        if (!is_writable($this->logoDir)) {
            return $this->json(['success' => false, 'error' => 'Dossier non accessible en écriture : ' . $this->logoDir], 500);
        }

        $file->move($this->logoDir, $filename);

        $config = $this->repo->getConfig();

        // Supprimer l'ancien logo si existant
        if ($config->getLogo()) {
            $oldPath = $this->logoDir . '/' . $config->getLogo();
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $config->setLogo($filename);
        $this->em->flush();

        return $this->json(['success' => true, 'data' => $config->toArray()]);
    }

    #[Route('/logo', name: 'delete_logo', methods: ['DELETE'])]
    public function deleteLogo(): JsonResponse
    {
        $config = $this->repo->getConfig();

        if ($config->getLogo()) {
            $path = $this->logoDir . '/' . $config->getLogo();
            if (file_exists($path)) {
                unlink($path);
            }
            $config->setLogo(null);
            $this->em->flush();
        }

        return $this->json(['success' => true, 'data' => $config->toArray()]);
    }
}
