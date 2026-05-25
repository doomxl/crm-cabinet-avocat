<?php

namespace App\Enum;

enum StatutFactureEnum: string
{
    case En_cours = 'En cours';
    case Payee = 'Payée';
    case Partiellement_payee = 'Partiellement payée';
    case Annulee = 'Annulée';
    case En_retard = 'En retard';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::En_cours => 'badge bg-primary',
            self::Payee => 'badge bg-success',
            self::Partiellement_payee => 'badge bg-warning text-dark',
            self::Annulee => 'badge bg-secondary',
            self::En_retard => 'badge bg-danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
