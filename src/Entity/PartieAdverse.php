<?php

namespace App\Entity;

use App\Enum\TypePartieAdverseEnum;
use App\Repository\PartieAdverseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartieAdverseRepository::class)]
#[ORM\Table(name: 'parties_adverses')]
#[ORM\HasLifecycleCallbacks]
class PartieAdverse
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'partiesAdverses')]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: false, onDelete: 'CASCADE')]
    private ?Dossier $dossier = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(name: 'type_partie', type: 'string', enumType: TypePartieAdverseEnum::class)]
    private TypePartieAdverseEnum $typePartie = TypePartieAdverseEnum::Personne_physique;

    #[ORM\Column(name: 'entreprise', type: 'string', length: 255, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'siret', type: 'string', length: 20, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(name: 'siren', type: 'string', length: 10, nullable: true)]
    private ?string $siren = null;

    #[ORM\Column(name: 'rcs', type: 'string', length: 100, nullable: true)]
    private ?string $rcs = null;

    #[ORM\Column(name: 'forme_juridique', type: 'string', length: 100, nullable: true)]
    private ?string $formeJuridique = null;

    #[ORM\Column(name: 'capital_social', type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private ?string $capitalSocial = null;

    #[ORM\Column(name: 'code_ape', type: 'string', length: 10, nullable: true)]
    private ?string $codeAPE = null;

    #[ORM\Column(name: 'representant_legal', type: 'string', length: 255, nullable: true)]
    private ?string $representantLegal = null;

    #[ORM\Column(name: 'qualite_representant', type: 'string', length: 100, nullable: true)]
    private ?string $qualiteRepresentant = null;

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'code_postal', type: 'string', length: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: 'avocat_adverse', type: 'string', length: 255, nullable: true)]
    private ?string $avocatAdverse = null;

    #[ORM\Column(name: 'barreau_adverse', type: 'string', length: 100, nullable: true)]
    private ?string $barreauAdverse = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
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
    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getTypePartie(): TypePartieAdverseEnum { return $this->typePartie; }
    public function setTypePartie(TypePartieAdverseEnum $typePartie): static { $this->typePartie = $typePartie; return $this; }
    public function getEntreprise(): ?string { return $this->entreprise; }
    public function setEntreprise(?string $entreprise): static { $this->entreprise = $entreprise; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getSiret(): ?string { return $this->siret; }
    public function setSiret(?string $siret): static { $this->siret = $siret; return $this; }
    public function getSiren(): ?string { return $this->siren; }
    public function setSiren(?string $siren): static { $this->siren = $siren; return $this; }
    public function getRcs(): ?string { return $this->rcs; }
    public function setRcs(?string $rcs): static { $this->rcs = $rcs; return $this; }
    public function getFormeJuridique(): ?string { return $this->formeJuridique; }
    public function setFormeJuridique(?string $formeJuridique): static { $this->formeJuridique = $formeJuridique; return $this; }
    public function getCapitalSocial(): ?string { return $this->capitalSocial; }
    public function setCapitalSocial(?string $capitalSocial): static { $this->capitalSocial = $capitalSocial; return $this; }
    public function getCodeAPE(): ?string { return $this->codeAPE; }
    public function setCodeAPE(?string $codeAPE): static { $this->codeAPE = $codeAPE; return $this; }
    public function getRepresentantLegal(): ?string { return $this->representantLegal; }
    public function setRepresentantLegal(?string $representantLegal): static { $this->representantLegal = $representantLegal; return $this; }
    public function getQualiteRepresentant(): ?string { return $this->qualiteRepresentant; }
    public function setQualiteRepresentant(?string $qualiteRepresentant): static { $this->qualiteRepresentant = $qualiteRepresentant; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $codePostal): static { $this->codePostal = $codePostal; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): static { $this->ville = $ville; return $this; }
    public function getAvocatAdverse(): ?string { return $this->avocatAdverse; }
    public function setAvocatAdverse(?string $avocatAdverse): static { $this->avocatAdverse = $avocatAdverse; return $this; }
    public function getBarreauAdverse(): ?string { return $this->barreauAdverse; }
    public function setBarreauAdverse(?string $barreauAdverse): static { $this->barreauAdverse = $barreauAdverse; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getNomComplet(): string
    {
        if ($this->typePartie === TypePartieAdverseEnum::Personne_morale && $this->entreprise) {
            return $this->entreprise;
        }
        return trim(($this->prenom ? $this->prenom . ' ' : '') . ($this->nom ?? ''));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'typePartie' => $this->typePartie->value,
            'entreprise' => $this->entreprise,
            'nomComplet' => $this->getNomComplet(),
            'notes' => $this->notes,
            'siret' => $this->siret,
            'siren' => $this->siren,
            'rcs' => $this->rcs,
            'formeJuridique' => $this->formeJuridique,
            'capitalSocial' => $this->capitalSocial ? (float)$this->capitalSocial : null,
            'codeAPE' => $this->codeAPE,
            'representantLegal' => $this->representantLegal,
            'qualiteRepresentant' => $this->qualiteRepresentant,
            'adresse' => $this->adresse,
            'codePostal' => $this->codePostal,
            'ville' => $this->ville,
            'avocatAdverse' => $this->avocatAdverse,
            'barreauAdverse' => $this->barreauAdverse,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
