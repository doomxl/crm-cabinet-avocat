<?php

namespace App\Enum;

enum TypeConventionEnum: string
{
    case fixe = 'fixe';
    case horaire = 'horaire';
    case pourcentage = 'pourcentage';
    case mixte = 'mixte';

    public function label(): string
    {
        return match($this) {
            self::fixe => 'Forfait fixe',
            self::horaire => 'Taux horaire',
            self::pourcentage => 'Pourcentage du résultat',
            self::mixte => 'Mixte',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
