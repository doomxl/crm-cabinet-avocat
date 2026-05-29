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

    #[ORM\Column(name: 'avocat_nom', type: 'string', length: 255, nullable: true)]
    private ?string $avocatNom = null;

    #[ORM\Column(name: 'avocat_barreau', type: 'string', length: 100, nullable: true)]
    private ?string $avocatBarreau = null;

    #[ORM\Column(name: 'avocat_numero', type: 'string', length: 50, nullable: true)]
    private ?string $avocatNumero = null;

    #[ORM\Column(name: 'couleurs_matiere', type: 'json')]
    private array $couleursMatiere = [];

    #[ORM\Column(name: 'couleurs_statut', type: 'json')]
    private array $couleursStatut = [];

    #[ORM\Column(name: 'couleurs_statut_acte', type: 'json')]
    private array $couleursStatutActe = [];

    #[ORM\Column(name: 'couleurs_statut_facture', type: 'json')]
    private array $couleursStatutFacture = [];

    #[ORM\Column(name: 'couleurs_conflit', type: 'json')]
    private array $couleursConflit = [];

    #[ORM\Column(name: 'couleurs_echeance', type: 'json')]
    private array $couleursEcheance = [];

    #[ORM\Column(name: 'matieres', type: 'json')]
    private array $matieres = [];

    #[ORM\Column(name: 'logo', type: 'string', length: 255, nullable: true)]
    private ?string $logo = null;

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
    public function getAvocatNom(): ?string { return $this->avocatNom; }
    public function setAvocatNom(?string $avocatNom): static { $this->avocatNom = $avocatNom; return $this; }
    public function getAvocatBarreau(): ?string { return $this->avocatBarreau; }
    public function setAvocatBarreau(?string $avocatBarreau): static { $this->avocatBarreau = $avocatBarreau; return $this; }
    public function getAvocatNumero(): ?string { return $this->avocatNumero; }
    public function setAvocatNumero(?string $avocatNumero): static { $this->avocatNumero = $avocatNumero; return $this; }
    public function getCouleursMatiere(): array { return $this->couleursMatiere; }
    public function setCouleursMatiere(array $couleursMatiere): static { $this->couleursMatiere = $couleursMatiere; return $this; }
    public function getCouleursStatut(): array { return $this->couleursStatut; }
    public function setCouleursStatut(array $couleursStatut): static { $this->couleursStatut = $couleursStatut; return $this; }
    public function getCouleursStatutActe(): array { return $this->couleursStatutActe; }
    public function setCouleursStatutActe(array $couleursStatutActe): static { $this->couleursStatutActe = $couleursStatutActe; return $this; }
    public function getCouleursStatutFacture(): array { return $this->couleursStatutFacture; }
    public function setCouleursStatutFacture(array $couleursStatutFacture): static { $this->couleursStatutFacture = $couleursStatutFacture; return $this; }
    public function getCouleursConflit(): array { return $this->couleursConflit; }
    public function setCouleursConflit(array $couleursConflit): static { $this->couleursConflit = $couleursConflit; return $this; }

    private static array $echeanceDefauts = [
        'expire'    => '#9CA3AF',
        'critique'  => '#F87171',
        'urgent'    => '#FDBA74',
        'attention' => '#FCD34D',
        'normal'    => '#60A5FA',
        'lointain'  => '#34D399',
    ];

    public function getCouleursEcheance(): array
    {
        $stored = $this->couleursEcheance;
        $result = [];
        foreach (self::$echeanceDefauts as $niveau => $defaut) {
            $result[$niveau] = $stored[$niveau] ?? $defaut;
        }
        return $result;
    }

    public static function getEcheanceDefauts(): array { return self::$echeanceDefauts; }
    public function setCouleursEcheance(array $couleursEcheance): static { $this->couleursEcheance = $couleursEcheance; return $this; }

    public function getMatieres(): array
    {
        if (empty($this->matieres)) {
            $defaults = [
                'Droit familial'     => '#F472B6',
                'Droit pénal'        => '#F87171',
                'Droit des affaires' => '#60A5FA',
                'Droit social'       => '#FDBA74',
                'Droit immobilier'   => '#34D399',
                'Droit administratif'=> '#A78BFA',
                'Droit international'=> '#22D3EE',
                'Autres'             => '#9CA3AF',
            ];
            $stored = $this->couleursMatiere;
            $result = [];
            foreach ($defaults as $label => $couleur) {
                $result[] = ['label' => $label, 'couleur' => $stored[$label] ?? $couleur];
            }
            return $result;
        }
        return $this->matieres;
    }

    public function setMatieres(array $matieres): static { $this->matieres = $matieres; return $this; }
    public function getLogo(): ?string { return $this->logo; }
    public function setLogo(?string $logo): static { $this->logo = $logo; return $this; }
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
            'avocatNom' => $this->avocatNom,
            'avocatBarreau' => $this->avocatBarreau,
            'avocatNumero' => $this->avocatNumero,
            'couleursMatiere' => $this->couleursMatiere,
            'couleursStatut'     => $this->couleursStatut,
            'couleursStatutActe'    => $this->couleursStatutActe,
            'couleursStatutFacture' => $this->couleursStatutFacture,
            'couleursConflit'        => $this->couleursConflit,
            'couleursEcheance'       => $this->getCouleursEcheance(),
            'matieres'               => $this->getMatieres(),
            'logo'              => $this->logo ? '/uploads/logo/' . $this->logo : null,
            'tauxHoraireDefaut' => (float)$this->tauxHoraireDefaut,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
