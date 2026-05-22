<?php

namespace App\Entity;

use App\Enum\StatutEcheanceEnum;
use App\Enum\TypeEcheanceEnum;
use App\Repository\EcheanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcheanceRepository::class)]
#[ORM\Table(name: 'echeances')]
#[ORM\HasLifecycleCallbacks]
class Echeance
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'echeances')]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: false, onDelete: 'CASCADE')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'type', type: 'string', enumType: TypeEcheanceEnum::class)]
    private TypeEcheanceEnum $type = TypeEcheanceEnum::Audience;

    #[ORM\Column(name: 'date', type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'statut', type: 'string', enumType: StatutEcheanceEnum::class)]
    private StatutEcheanceEnum $statut = StatutEcheanceEnum::A_venir;

    #[ORM\ManyToOne(targetEntity: Rendezvous::class)]
    #[ORM\JoinColumn(name: 'rendezvous_id', nullable: true, onDelete: 'SET NULL')]
    private ?Rendezvous $rendezvous = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
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
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getType(): TypeEcheanceEnum { return $this->type; }
    public function setType(TypeEcheanceEnum $type): static { $this->type = $type; return $this; }
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getStatut(): StatutEcheanceEnum { return $this->statut; }
    public function setStatut(StatutEcheanceEnum $statut): static { $this->statut = $statut; return $this; }
    public function getRendezvous(): ?Rendezvous { return $this->rendezvous; }
    public function setRendezvous(?Rendezvous $rendezvous): static { $this->rendezvous = $rendezvous; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getJoursRestants(): int
    {
        $now = new \DateTimeImmutable('today');
        $diff = $now->diff($this->date);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'type' => $this->type->value,
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'statut' => $this->statut->value,
            'joursRestants' => $this->getJoursRestants(),
            'rendezvousId' => $this->rendezvous?->getId(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
