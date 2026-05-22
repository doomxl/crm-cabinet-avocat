<?php

namespace App\Entity;

use App\Repository\LigneFactureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneFactureRepository::class)]
#[ORM\Table(name: 'lignes_facture')]
class LigneFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Facture::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'facture_id', nullable: false, onDelete: 'CASCADE')]
    private ?Facture $facture = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position = 0;

    #[ORM\Column(name: 'description', type: 'text')]
    private string $description = '';

    #[ORM\Column(name: 'quantite', type: 'decimal', precision: 10, scale: 2)]
    private string $quantite = '1.00';

    #[ORM\Column(name: 'prix_unitaire', type: 'decimal', precision: 12, scale: 2)]
    private string $prixUnitaire = '0.00';

    #[ORM\Column(name: 'unite', type: 'string', length: 50)]
    private string $unite = 'forfait';

    public function getId(): ?int { return $this->id; }
    public function getFacture(): ?Facture { return $this->facture; }
    public function setFacture(?Facture $facture): static { $this->facture = $facture; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getQuantite(): string { return $this->quantite; }
    public function setQuantite(string $quantite): static { $this->quantite = $quantite; return $this; }
    public function getPrixUnitaire(): string { return $this->prixUnitaire; }
    public function setPrixUnitaire(string $prixUnitaire): static { $this->prixUnitaire = $prixUnitaire; return $this; }
    public function getUnite(): string { return $this->unite; }
    public function setUnite(string $unite): static { $this->unite = $unite; return $this; }

    public function getMontantTotal(): float
    {
        return (float)$this->quantite * (float)$this->prixUnitaire;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'factureId' => $this->facture?->getId(),
            'position' => $this->position,
            'description' => $this->description,
            'quantite' => (float)$this->quantite,
            'prixUnitaire' => (float)$this->prixUnitaire,
            'unite' => $this->unite,
            'montantTotal' => $this->getMontantTotal(),
        ];
    }
}
