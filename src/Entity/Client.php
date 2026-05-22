<?php

namespace App\Entity;

use App\Enum\TypeClientEnum;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'clients')]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'code_client', type: 'string', length: 50, unique: true, nullable: true)]
    private ?string $codeClient = null;

    #[ORM\Column(name: 'type_client', type: 'string', enumType: TypeClientEnum::class)]
    private TypeClientEnum $typeClient = TypeClientEnum::Particulier;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'prenom', type: 'string', length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'telephone', type: 'string', length: 50, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: 'code_postal', type: 'string', length: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(name: 'intitule', type: 'string', length: 255, nullable: true)]
    private ?string $intitule = null;

    #[ORM\Column(name: 'siret', type: 'string', length: 20, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(name: 'type_societe', type: 'string', length: 100, nullable: true)]
    private ?string $typeSociete = null;

    #[ORM\Column(name: 'code_ape', type: 'string', length: 10, nullable: true)]
    private ?string $codeAPE = null;

    #[ORM\Column(name: 'cph', type: 'string', length: 100, nullable: true)]
    private ?string $cph = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Dossier::class)]
    private Collection $dossiers;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Rendezvous::class)]
    private Collection $rendezvous;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->rendezvous = new ArrayCollection();
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
    public function getCodeClient(): ?string { return $this->codeClient; }
    public function setCodeClient(?string $codeClient): static { $this->codeClient = $codeClient; return $this; }
    public function getTypeClient(): TypeClientEnum { return $this->typeClient; }
    public function setTypeClient(TypeClientEnum $typeClient): static { $this->typeClient = $typeClient; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): static { $this->ville = $ville; return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $codePostal): static { $this->codePostal = $codePostal; return $this; }
    public function getIntitule(): ?string { return $this->intitule; }
    public function setIntitule(?string $intitule): static { $this->intitule = $intitule; return $this; }
    public function getSiret(): ?string { return $this->siret; }
    public function setSiret(?string $siret): static { $this->siret = $siret; return $this; }
    public function getTypeSociete(): ?string { return $this->typeSociete; }
    public function setTypeSociete(?string $typeSociete): static { $this->typeSociete = $typeSociete; return $this; }
    public function getCodeAPE(): ?string { return $this->codeAPE; }
    public function setCodeAPE(?string $codeAPE): static { $this->codeAPE = $codeAPE; return $this; }
    public function getCph(): ?string { return $this->cph; }
    public function setCph(?string $cph): static { $this->cph = $cph; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getNomComplet(): string
    {
        if ($this->typeClient === TypeClientEnum::Entreprise && $this->intitule) {
            return $this->intitule;
        }
        return trim(($this->prenom ? $this->prenom . ' ' : '') . $this->nom);
    }

    public function getDossiers(): Collection { return $this->dossiers; }
    public function getFactures(): Collection { return $this->factures; }
    public function getRendezvous(): Collection { return $this->rendezvous; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'codeClient' => $this->codeClient,
            'typeClient' => $this->typeClient->value,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nomComplet' => $this->getNomComplet(),
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'codePostal' => $this->codePostal,
            'intitule' => $this->intitule,
            'siret' => $this->siret,
            'typeSociete' => $this->typeSociete,
            'codeAPE' => $this->codeAPE,
            'cph' => $this->cph,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
