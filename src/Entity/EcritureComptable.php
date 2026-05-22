<?php

namespace App\Entity;

use App\Enum\CategorieEcritureEnum;
use App\Enum\ModeReglementEnum;
use App\Enum\TypeEcritureEnum;
use App\Repository\EcritureComptableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcritureComptableRepository::class)]
#[ORM\Table(name: 'ecritures_comptables')]
#[ORM\HasLifecycleCallbacks]
class EcritureComptable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'date', type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(name: 'type', type: 'string', enumType: TypeEcritureEnum::class)]
    private TypeEcritureEnum $type = TypeEcritureEnum::recette;

    #[ORM\Column(name: 'categorie', type: 'string', enumType: CategorieEcritureEnum::class)]
    private CategorieEcritureEnum $categorie = CategorieEcritureEnum::AA;

    #[ORM\Column(name: 'libelle', type: 'text')]
    private string $libelle = '';

    #[ORM\Column(name: 'montant', type: 'decimal', precision: 12, scale: 2)]
    private string $montant = '0.00';

    #[ORM\Column(name: 'mode_reglement', type: 'string', enumType: ModeReglementEnum::class, nullable: true)]
    private ?ModeReglementEnum $modeReglement = null;

    #[ORM\Column(name: 'reference', type: 'string', length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: Facture::class)]
    #[ORM\JoinColumn(name: 'facture_id', nullable: true, onDelete: 'SET NULL')]
    private ?Facture $facture = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

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
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }
    public function getType(): TypeEcritureEnum { return $this->type; }
    public function setType(TypeEcritureEnum $type): static { $this->type = $type; return $this; }
    public function getCategorie(): CategorieEcritureEnum { return $this->categorie; }
    public function setCategorie(CategorieEcritureEnum $categorie): static { $this->categorie = $categorie; return $this; }
    public function getLibelle(): string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = $libelle; return $this; }
    public function getMontant(): string { return $this->montant; }
    public function setMontant(string $montant): static { $this->montant = $montant; return $this; }
    public function getModeReglement(): ?ModeReglementEnum { return $this->modeReglement; }
    public function setModeReglement(?ModeReglementEnum $modeReglement): static { $this->modeReglement = $modeReglement; return $this; }
    public function getReference(): ?string { return $this->reference; }
    public function setReference(?string $reference): static { $this->reference = $reference; return $this; }
    public function getFacture(): ?Facture { return $this->facture; }
    public function setFacture(?Facture $facture): static { $this->facture = $facture; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'categorie' => $this->categorie->value,
            'categorieLabel' => $this->categorie->label(),
            'libelle' => $this->libelle,
            'montant' => (float)$this->montant,
            'modeReglement' => $this->modeReglement?->value,
            'reference' => $this->reference,
            'factureId' => $this->facture?->getId(),
            'factureNumero' => $this->facture?->getNumero(),
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
