<?php

namespace App\Entity;

use App\Repository\CabinetConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CabinetConfigRepository::class)]
#[ORM\Table(name: 'cabinet_config')]
#[ORM\HasLifecycleCallbacks]
class CabinetConfig
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id = 1;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = "Cabinet d'Avocat";

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'code_postal', type: 'string', length: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: 'telephone', type: 'string', length: 50, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'siret', type: 'string', length: 20, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(name: 'couleurs_matiere', type: 'json')]
    private array $couleursMatiere = [];

    #[ORM\Column(name: 'taux_horaire_defaut', type: 'decimal', precision: 10, scale: 2)]
    private string $tauxHoraireDefaut = '150.00';

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->couleursMatiere = [
            'Droit familial' => '#EC4899',
            'Droit pénal' => '#EF4444',
            'Droit des affaires' => '#1E40AF',
            'Droit social' => '#F97316',
            'Droit immobilier' => '#059669',
            'Droit administratif' => '#7C3AED',
            'Droit international' => '#06B6D4',
            'Autres' => '#6B7280',
        ];
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): static { $this->id = $id; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $codePostal): static { $this->codePostal = $codePostal; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): static { $this->ville = $ville; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getSiret(): ?string { return $this->siret; }
    public function setSiret(?string $siret): static { $this->siret = $siret; return $this; }
    public function getCouleursMatiere(): array { return $this->couleursMatiere; }
    public function setCouleursMatiere(array $couleursMatiere): static { $this->couleursMatiere = $couleursMatiere; return $this; }
    public function getTauxHoraireDefaut(): string { return $this->tauxHoraireDefaut; }
    public function setTauxHoraireDefaut(string $tauxHoraireDefaut): static { $this->tauxHoraireDefaut = $tauxHoraireDefaut; return $this; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'codePostal' => $this->codePostal,
            'ville' => $this->ville,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'siret' => $this->siret,
            'couleursMatiere' => $this->couleursMatiere,
            'tauxHoraireDefaut' => (float)$this->tauxHoraireDefaut,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
