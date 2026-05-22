<?php

namespace App\Entity;

use App\Enum\ModeReglementEnum;
use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiements')]
#[ORM\HasLifecycleCallbacks]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Facture::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(name: 'facture_id', nullable: false, onDelete: 'CASCADE')]
    private ?Facture $facture = null;

    #[ORM\Column(name: 'date_paiement', type: 'date_immutable')]
    private \DateTimeImmutable $datePaiement;

    #[ORM\Column(name: 'montant', type: 'decimal', precision: 12, scale: 2)]
    private string $montant = '0.00';

    #[ORM\Column(name: 'mode_reglement', type: 'string', enumType: ModeReglementEnum::class, nullable: true)]
    private ?ModeReglementEnum $modeReglement = null;

    #[ORM\Column(name: 'reference', type: 'string', length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->datePaiement = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFacture(): ?Facture { return $this->facture; }
    public function setFacture(?Facture $facture): static { $this->facture = $facture; return $this; }
    public function getDatePaiement(): \DateTimeImmutable { return $this->datePaiement; }
    public function setDatePaiement(\DateTimeImmutable $datePaiement): static { $this->datePaiement = $datePaiement; return $this; }
    public function getMontant(): string { return $this->montant; }
    public function setMontant(string $montant): static { $this->montant = $montant; return $this; }
    public function getModeReglement(): ?ModeReglementEnum { return $this->modeReglement; }
    public function setModeReglement(?ModeReglementEnum $modeReglement): static { $this->modeReglement = $modeReglement; return $this; }
    public function getReference(): ?string { return $this->reference; }
    public function setReference(?string $reference): static { $this->reference = $reference; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'factureId' => $this->facture?->getId(),
            'datePaiement' => $this->datePaiement->format('Y-m-d'),
            'montant' => (float)$this->montant,
            'modeReglement' => $this->modeReglement?->value,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
