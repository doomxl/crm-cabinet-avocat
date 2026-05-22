<?php

namespace App\Entity;

use App\Enum\MatiereEnum;
use App\Enum\StatutDossierEnum;
use App\Enum\TypeConventionEnum;
use App\Enum\TypePartieAdverseEnum;
use App\Repository\DossierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DossierRepository::class)]
#[ORM\Table(name: 'dossiers')]
#[ORM\HasLifecycleCallbacks]
class Dossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'dossiers')]
    #[ORM\JoinColumn(name: 'client_id', nullable: false, onDelete: 'RESTRICT')]
    private ?Client $client = null;

    #[ORM\Column(name: 'titre', type: 'string', length: 500)]
    private string $titre = '';

    #[ORM\Column(name: 'couleur', type: 'string', length: 20)]
    private string $couleur = '#3B82F6';

    #[ORM\Column(name: 'statut', type: 'string', enumType: StatutDossierEnum::class)]
    private StatutDossierEnum $statut = StatutDossierEnum::En_cours;

    #[ORM\Column(name: 'matiere', type: 'string', enumType: MatiereEnum::class, nullable: true)]
    private ?MatiereEnum $matiere = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'date_ouverture', type: 'date_immutable')]
    private \DateTimeImmutable $dateOuverture;

    #[ORM\Column(name: 'partie_adverse_nom', type: 'string', length: 255, nullable: true)]
    private ?string $partieAdverseNom = null;

    #[ORM\Column(name: 'partie_adverse_prenom', type: 'string', length: 255, nullable: true)]
    private ?string $partieAdversePrenom = null;

    #[ORM\Column(name: 'partie_adverse_type', type: 'string', enumType: TypePartieAdverseEnum::class)]
    private TypePartieAdverseEnum $partieAdverseType = TypePartieAdverseEnum::Personne_physique;

    #[ORM\Column(name: 'partie_adverse_entreprise', type: 'string', length: 255, nullable: true)]
    private ?string $partieAdverseEntreprise = null;

    #[ORM\Column(name: 'convention_sous_branche', type: 'string', length: 100, nullable: true)]
    private ?string $conventionSousBranche = null;

    #[ORM\Column(name: 'convention_type_honoraire', type: 'string', enumType: TypeConventionEnum::class, nullable: true)]
    private ?TypeConventionEnum $conventionTypeHonoraire = null;

    #[ORM\Column(name: 'convention_montant_fixe', type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $conventionMontantFixe = null;

    #[ORM\Column(name: 'convention_pourcentage_resultat', type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $conventionPourcentageResultat = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: PartieAdverse::class, cascade: ['persist', 'remove'])]
    private Collection $partiesAdverses;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: Echeance::class, cascade: ['persist', 'remove'])]
    private Collection $echeances;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: Chronologie::class, cascade: ['persist', 'remove'])]
    private Collection $chronologies;

    #[ORM\OneToMany(mappedBy: 'dossier', targetEntity: DocumentDossier::class, cascade: ['persist', 'remove'])]
    private Collection $documents;

    public function __construct()
    {
        $this->partiesAdverses = new ArrayCollection();
        $this->echeances = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->chronologies = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->dateOuverture = new \DateTimeImmutable();
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
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getCouleur(): string { return $this->couleur; }
    public function setCouleur(string $couleur): static { $this->couleur = $couleur; return $this; }
    public function getStatut(): StatutDossierEnum { return $this->statut; }
    public function setStatut(StatutDossierEnum $statut): static { $this->statut = $statut; return $this; }
    public function getMatiere(): ?MatiereEnum { return $this->matiere; }
    public function setMatiere(?MatiereEnum $matiere): static { $this->matiere = $matiere; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getDateOuverture(): \DateTimeImmutable { return $this->dateOuverture; }
    public function setDateOuverture(\DateTimeImmutable $dateOuverture): static { $this->dateOuverture = $dateOuverture; return $this; }
    public function getPartieAdverseNom(): ?string { return $this->partieAdverseNom; }
    public function setPartieAdverseNom(?string $partieAdverseNom): static { $this->partieAdverseNom = $partieAdverseNom; return $this; }
    public function getPartieAdversePrenom(): ?string { return $this->partieAdversePrenom; }
    public function setPartieAdversePrenom(?string $partieAdversePrenom): static { $this->partieAdversePrenom = $partieAdversePrenom; return $this; }
    public function getPartieAdverseType(): TypePartieAdverseEnum { return $this->partieAdverseType; }
    public function setPartieAdverseType(TypePartieAdverseEnum $partieAdverseType): static { $this->partieAdverseType = $partieAdverseType; return $this; }
    public function getPartieAdverseEntreprise(): ?string { return $this->partieAdverseEntreprise; }
    public function setPartieAdverseEntreprise(?string $partieAdverseEntreprise): static { $this->partieAdverseEntreprise = $partieAdverseEntreprise; return $this; }
    public function getConventionSousBranche(): ?string { return $this->conventionSousBranche; }
    public function setConventionSousBranche(?string $conventionSousBranche): static { $this->conventionSousBranche = $conventionSousBranche; return $this; }
    public function getConventionTypeHonoraire(): ?TypeConventionEnum { return $this->conventionTypeHonoraire; }
    public function setConventionTypeHonoraire(?TypeConventionEnum $conventionTypeHonoraire): static { $this->conventionTypeHonoraire = $conventionTypeHonoraire; return $this; }
    public function getConventionMontantFixe(): ?string { return $this->conventionMontantFixe; }
    public function setConventionMontantFixe(?string $conventionMontantFixe): static { $this->conventionMontantFixe = $conventionMontantFixe; return $this; }
    public function getConventionPourcentageResultat(): ?string { return $this->conventionPourcentageResultat; }
    public function setConventionPourcentageResultat(?string $conventionPourcentageResultat): static { $this->conventionPourcentageResultat = $conventionPourcentageResultat; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getPartiesAdverses(): Collection { return $this->partiesAdverses; }
    public function getEcheances(): Collection { return $this->echeances; }
    public function getFactures(): Collection { return $this->factures; }
    public function getChronologies(): Collection { return $this->chronologies; }
    public function getDocuments(): Collection { return $this->documents; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'titre' => $this->titre,
            'couleur' => $this->couleur,
            'statut' => $this->statut->value,
            'matiere' => $this->matiere?->value,
            'notes' => $this->notes,
            'dateOuverture' => $this->dateOuverture->format('Y-m-d'),
            'partieAdverseNom' => $this->partieAdverseNom,
            'partieAdversePrenom' => $this->partieAdversePrenom,
            'partieAdverseType' => $this->partieAdverseType->value,
            'partieAdverseEntreprise' => $this->partieAdverseEntreprise,
            'conventionSousBranche' => $this->conventionSousBranche,
            'conventionTypeHonoraire' => $this->conventionTypeHonoraire?->value,
            'conventionMontantFixe' => $this->conventionMontantFixe ? (float)$this->conventionMontantFixe : null,
            'conventionPourcentageResultat' => $this->conventionPourcentageResultat ? (float)$this->conventionPourcentageResultat : null,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
