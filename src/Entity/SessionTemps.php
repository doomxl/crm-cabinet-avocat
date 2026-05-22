<?php

namespace App\Entity;

use App\Repository\SessionTempsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionTempsRepository::class)]
#[ORM\Table(name: 'sessions_temps')]
#[ORM\HasLifecycleCallbacks]
class SessionTemps
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: true, onDelete: 'SET NULL')]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\Column(name: 'taux_horaire', type: 'decimal', precision: 10, scale: 2)]
    private string $tauxHoraire = '150.00';

    #[ORM\Column(name: 'debut', type: 'datetime_immutable')]
    private \DateTimeImmutable $debut;

    #[ORM\Column(name: 'fin', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $fin = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->debut = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getTauxHoraire(): string { return $this->tauxHoraire; }
    public function setTauxHoraire(string $tauxHoraire): static { $this->tauxHoraire = $tauxHoraire; return $this; }
    public function getDebut(): \DateTimeImmutable { return $this->debut; }
    public function setDebut(\DateTimeImmutable $debut): static { $this->debut = $debut; return $this; }
    public function getFin(): ?\DateTimeImmutable { return $this->fin; }
    public function setFin(?\DateTimeImmutable $fin): static { $this->fin = $fin; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getDureeMinutes(): ?int
    {
        if (!$this->fin) {
            return null;
        }
        return (int)(($this->fin->getTimestamp() - $this->debut->getTimestamp()) / 60);
    }

    public function getMontantFacturable(): ?float
    {
        $duree = $this->getDureeMinutes();
        if ($duree === null) return null;
        return round(($duree / 60) * (float)$this->tauxHoraire, 2);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'tauxHoraire' => (float)$this->tauxHoraire,
            'debut' => $this->debut->format('Y-m-d H:i:s'),
            'fin' => $this->fin?->format('Y-m-d H:i:s'),
            'dureeMinutes' => $this->getDureeMinutes(),
            'montantFacturable' => $this->getMontantFacturable(),
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
