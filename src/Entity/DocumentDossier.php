<?php

namespace App\Entity;

use App\Repository\DocumentDossierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentDossierRepository::class)]
#[ORM\Table(name: 'documents_dossier')]
class DocumentDossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: false, onDelete: 'CASCADE')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'categorie', type: 'string', length: 100, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(name: 'mime_type', type: 'string', length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(name: 'taille', type: 'bigint', nullable: true)]
    private ?int $taille = null;

    #[ORM\Column(name: 'chemin', type: 'text', nullable: true)]
    private ?string $chemin = null;

    #[ORM\Column(name: 'uploaded_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $uploadedAt;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): static { $this->categorie = $categorie; return $this; }
    public function getMimeType(): ?string { return $this->mimeType; }
    public function setMimeType(?string $mimeType): static { $this->mimeType = $mimeType; return $this; }
    public function getTaille(): ?int { return $this->taille; }
    public function setTaille(?int $taille): static { $this->taille = $taille; return $this; }
    public function getChemin(): ?string { return $this->chemin; }
    public function setChemin(?string $chemin): static { $this->chemin = $chemin; return $this; }
    public function getUploadedAt(): \DateTimeImmutable { return $this->uploadedAt; }
    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static { $this->uploadedAt = $uploadedAt; return $this; }

    public function getTailleHumaine(): string
    {
        if (!$this->taille) return '0 o';
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        $size = $this->taille;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'nom' => $this->nom,
            'categorie' => $this->categorie,
            'mimeType' => $this->mimeType,
            'taille' => $this->taille,
            'tailleHumaine' => $this->getTailleHumaine(),
            'chemin' => $this->chemin,
            'uploadedAt' => $this->uploadedAt->format('Y-m-d H:i:s'),
        ];
    }
}
