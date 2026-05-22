<?php

namespace App\Entity;

use App\Repository\VerificationConflitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VerificationConflitRepository::class)]
#[ORM\Table(name: 'verifications_conflit')]
class VerificationConflit
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(name: 'dossier_id', nullable: true, onDelete: 'SET NULL')]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\Column(name: 'partie_adverse_nom', type: 'string', length: 255, nullable: true)]
    private ?string $partieAdverseNom = null;

    #[ORM\Column(name: 'resultat', type: 'text', nullable: true)]
    private ?string $resultat = null;

    #[ORM\Column(name: 'date_verification', type: 'datetime_immutable')]
    private \DateTimeImmutable $dateVerification;

    public function __construct()
    {
        $this->dateVerification = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(?Dossier $dossier): static { $this->dossier = $dossier; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getPartieAdverseNom(): ?string { return $this->partieAdverseNom; }
    public function setPartieAdverseNom(?string $partieAdverseNom): static { $this->partieAdverseNom = $partieAdverseNom; return $this; }
    public function getResultat(): ?string { return $this->resultat; }
    public function setResultat(?string $resultat): static { $this->resultat = $resultat; return $this; }
    public function getDateVerification(): \DateTimeImmutable { return $this->dateVerification; }
    public function setDateVerification(\DateTimeImmutable $dateVerification): static { $this->dateVerification = $dateVerification; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dossierId' => $this->dossier?->getId(),
            'dossierTitre' => $this->dossier?->getTitre(),
            'clientId' => $this->client?->getId(),
            'clientNom' => $this->client?->getNomComplet(),
            'partieAdverseNom' => $this->partieAdverseNom,
            'resultat' => $this->resultat,
            'dateVerification' => $this->dateVerification->format('Y-m-d H:i:s'),
        ];
    }
}
