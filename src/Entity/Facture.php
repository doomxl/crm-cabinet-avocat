<?php

namespace App\Entity;

use App\Enum\ModeReglementEnum;
use App\Enum\StatutFactureEnum;
use App\Enum\TypeDocumentEnum;
use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\Table(name: 'factures')]
#[ORM\HasLifecycleCallbacks]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'numero', type: 'string', length: 50, unique: true)]
    private string $numero = '';

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'factures')]
    #[ORM\JoinColumn(name: 'client_id', nullable: true, onDelete: 'RESTRICT')]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'factures')]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: true, onDelete: 'SET NULL')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'type_document', type: 'string', enumType: TypeDocumentEnum::class)]
    private TypeDocumentEnum $typeDocument = TypeDocumentEnum::Note_honoraires;

    #[ORM\Column(name: 'montant_ht', type: 'decimal', precision: 12, scale: 2)]
    private string $montantHt = '0.00';

    #[ORM\Column(name: 'taux_tva', type: 'decimal', precision: 5, scale: 2)]
    private string $tauxTva = '20.00';

    #[ORM\Column(name: 'provision', type: 'decimal', precision: 12, scale: 2)]
    private string $provision = '0.00';

    #[ORM\Column(name: 'date_emission', type: 'date_immutable')]
    private \DateTimeImmutable $dateEmission;

    #[ORM\Column(name: 'statut', type: 'string', enumType: StatutFactureEnum::class)]
    private StatutFactureEnum $statut = StatutFactureEnum::En_cours;

    #[ORM\Column(name: 'mode_reglement', type: 'string', enumType: ModeReglementEnum::class)]
    private ModeReglementEnum $modeReglement = ModeReglementEnum::Virement;

    #[ORM\Column(name: 'date_relance', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateRelance = null;

    #[ORM\Column(name: 'nombre_relances', type: 'integer')]
    private int $nombreRelances = 0;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: LigneFacture::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $lignes;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: Paiement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $paiements;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->dateEmission = new \DateTimeImmutable();
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
    public function getNumero(): string { return $this->numero; }
    public function setNumero(string $numero): static { $this->numero = $numero; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getTypeDocument(): TypeDocumentEnum { return $this->typeDocument; }
    public function setTypeDocument(TypeDocumentEnum $typeDocument): static { $this->typeDocument = $typeDocument; return $this; }
    public function getMontantHt(): string { return $this->montantHt; }
    public function setMontantHt(string $montantHt): static { $this->montantHt = $montantHt; return $this; }
    public function getTauxTva(): string { return $this->tauxTva; }
    public function setTauxTva(string $tauxTva): static { $this->tauxTva = $tauxTva; return $this; }
    public function getProvision(): string { return $this->provision; }
    public function setProvision(string $provision): static { $this->provision = $provision; return $this; }
    public function getDateEmission(): \DateTimeImmutable { return $this->dateEmission; }
    public function setDateEmission(\DateTimeImmutable $dateEmission): static { $this->dateEmission = $dateEmission; return $this; }
    public function getStatut(): StatutFactureEnum { return $this->statut; }
    public function setStatut(StatutFactureEnum $statut): static { $this->statut = $statut; return $this; }
    public function getModeReglement(): ModeReglementEnum { return $this->modeReglement; }
    public function setModeReglement(ModeReglementEnum $modeReglement): static { $this->modeReglement = $modeReglement; return $this; }
    public function getDateRelance(): ?\DateTimeImmutable { return $this->dateRelance; }
    public function setDateRelance(?\DateTimeImmutable $dateRelance): static { $this->dateRelance = $dateRelance; return $this; }
    public function getNombreRelances(): int { return $this->nombreRelances; }
    public function setNombreRelances(int $nombreRelances): static { $this->nombreRelances = $nombreRelances; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getLignes(): Collection { return $this->lignes; }
    public function getPaiements(): Collection { return $this->paiements; }

    public function getMontantTtc(): float
    {
        return (float)$this->montantHt * (1 + (float)$this->tauxTva / 100);
    }

    public function getMontantPaye(): float
    {
        $total = 0.0;
        foreach ($this->paiements as $paiement) {
            $total += (float)$paiement->getMontant();
        }
        return $total;
    }

    public function getSoldeRestant(): float
    {
        return $this->getMontantTtc() - $this->getMontantPaye() - (float)$this->provision;
    }

    public function addLigne(LigneFacture $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setFacture($this);
        }
        return $this;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setFacture($this);
        }
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'typeDocument' => $this->typeDocument->value,
            'montantHt' => (float)$this->montantHt,
            'tauxTva' => (float)$this->tauxTva,
            'montantTtc' => $this->getMontantTtc(),
            'provision' => (float)$this->provision,
            'montantPaye' => $this->getMontantPaye(),
            'soldeRestant' => $this->getSoldeRestant(),
            'dateEmission' => $this->dateEmission->format('Y-m-d'),
            'statut' => $this->statut->value,
            'modeReglement' => $this->modeReglement->value,
            'dateRelance' => $this->dateRelance?->format('Y-m-d'),
            'nombreRelances' => $this->nombreRelances,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
