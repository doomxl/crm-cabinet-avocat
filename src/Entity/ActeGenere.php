<?php

namespace App\Entity;

use App\Enum\StatutActeEnum;
use App\Repository\ActeGenereRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActeGenereRepository::class)]
#[ORM\Table(name: 'actes_generes')]
class ActeGenere
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ModeleActe::class)]
    #[ORM\JoinColumn(name: 'modele_id', nullable: true, onDelete: 'SET NULL')]
    private ?ModeleActe $modele = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: true, onDelete: 'SET NULL')]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'contenu', type: 'text', nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(name: 'date_generation', type: 'datetime_immutable')]
    private \DateTimeImmutable $dateGeneration;

    #[ORM\Column(name: 'statut', type: 'string', enumType: StatutActeEnum::class)]
    private StatutActeEnum $statut = StatutActeEnum::Brouillon;

    public function __construct()
    {
        $this->dateGeneration = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getModele(): ?ModeleActe { return $this->modele; }
    public function setModele(?ModeleActe $modele): static { $this->modele = $modele; return $this; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }
    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(?string $contenu): static { $this->contenu = $contenu; return $this; }
    public function getDateGeneration(): \DateTimeImmutable { return $this->dateGeneration; }
    public function setDateGeneration(\DateTimeImmutable $dateGeneration): static { $this->dateGeneration = $dateGeneration; return $this; }
    public function getStatut(): StatutActeEnum { return $this->statut; }
    public function setStatut(StatutActeEnum $statut): static { $this->statut = $statut; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'modeleId' => $this->modele?->getId(),
            'modeleNom' => $this->modele?->getNom(),
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'nom' => $this->nom,
            'contenu' => $this->contenu,
            'dateGeneration' => $this->dateGeneration->format('Y-m-d H:i:s'),
            'statut' => $this->statut->value,
            'statutBadge' => $this->statut->badgeClass(),
        ];
    }
}
