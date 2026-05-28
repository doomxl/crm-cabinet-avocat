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
            self::En_cours            => 'badge badge-soft-blue',
            self::Payee               => 'badge badge-soft-green',
            self::Partiellement_payee => 'badge badge-soft-orange',
            self::Annulee             => 'badge badge-soft-gray',
            self::En_retard           => 'badge badge-soft-red',
        };
    }

    public function couleurDefaut(): string
    {
        return match($this) {
            self::En_cours            => '#60A5FA',
            self::Payee               => '#34D399',
            self::Partiellement_payee => '#FDBA74',
            self::Annulee             => '#9CA3AF',
            self::En_retard           => '#F87171',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
