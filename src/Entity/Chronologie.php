<?php

namespace App\Entity;

use App\Enum\TypeEvenementEnum;
use App\Repository\ChronologieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChronologieRepository::class)]
#[ORM\Table(name: 'chronologie')]
#[ORM\HasLifecycleCallbacks]
class Chronologie
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'chronologies')]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: false, onDelete: 'CASCADE')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'type', type: 'string', enumType: TypeEvenementEnum::class)]
    private TypeEvenementEnum $type = TypeEvenementEnum::note;

    #[ORM\Column(name: 'titre', type: 'string', length: 255)]
    private string $titre = '';

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'date', type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(name: 'auto', type: 'boolean')]
    private bool $auto = false;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
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
    public function getType(): TypeEvenementEnum { return $this->type; }
    public function setType(TypeEvenementEnum $type): static { $this->type = $type; return $this; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }
    public function isAuto(): bool { return $this->auto; }
    public function setAuto(bool $auto): static { $this->auto = $auto; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'typeIcon' => $this->type->icon(),
            'typeCouleur' => $this->type->couleur(),
            'titre' => $this->titre,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d H:i:s'),
            'auto' => $this->auto,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
