<?php

namespace App\Enum;

enum StatutEcheanceEnum: string
{
    case A_venir = 'À venir';
    case En_cours = 'En cours';
    case Terminee = 'Terminée';
    case Annulee = 'Annulée';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::A_venir => 'badge bg-info text-dark',
            self::En_cours => 'badge bg-primary',
            self::Terminee => 'badge bg-success',
            self::Annulee => 'badge bg-secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
