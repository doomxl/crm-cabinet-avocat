<?php

namespace App\Enum;

enum StatutDossierEnum: string
{
    case En_cours = 'En cours';
    case Clos = 'Clos';
    case Suspendu = 'Suspendu';
    case Archive = 'Archivé';

    public function label(): string
    {
        return match($this) {
            self::En_cours => 'En cours',
            self::Clos => 'Clos',
            self::Suspendu => 'Suspendu',
            self::Archive => 'Archivé',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::En_cours => 'badge bg-primary',
            self::Clos => 'badge bg-secondary',
            self::Suspendu => 'badge bg-warning text-dark',
            self::Archive => 'badge bg-dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
