<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
#[ORM\Table(name: 'rendezvous')]
#[ORM\HasLifecycleCallbacks]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'rendezvous')]
    #[ORM\JoinColumn(name: 'client_id', nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: true, onDelete: 'SET NULL')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'date', type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(name: 'heure', type: 'time_mutable')]
    private \DateTime $heure;

    #[ORM\Column(name: 'duree', type: 'integer')]
    private int $duree = 60;

    #[ORM\Column(name: 'type', type: 'string', length: 100)]
    private string $type = 'Consultation';

    #[ORM\Column(name: 'lieu', type: 'string', length: 255)]
    private string $lieu = 'Cabinet';

    #[ORM\Column(name: 'lieu_autre', type: 'string', length: 255, nullable: true)]
    private ?string $lieuAutre = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->heure = new \DateTime('09:00:00');
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }
    public function getHeure(): \DateTime { return $this->heure; }
    public function setHeure(\DateTime $heure): static { $this->heure = $heure; return $this; }
    public function getDuree(): int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getLieu(): string { return $this->lieu; }
    public function setLieu(string $lieu): static { $this->lieu = $lieu; return $this; }
    public function getLieuAutre(): ?string { return $this->lieuAutre; }
    public function setLieuAutre(?string $lieuAutre): static { $this->lieuAutre = $lieuAutre; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'date' => $this->date->format('Y-m-d'),
            'heure' => $this->heure->format('H:i'),
            'duree' => $this->duree,
            'type' => $this->type,
            'lieu' => $this->lieu,
            'lieuAutre' => $this->lieuAutre,
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
