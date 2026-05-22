<?php

namespace App\Enum;

enum TypeEcritureEnum: string
{
    case recette = 'recette';
    case depense = 'depense';

    public function label(): string
    {
        return match($this) {
            self::recette => 'Recette',
            self::depense => 'Dépense',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::recette => 'badge bg-success',
            self::depense => 'badge bg-danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
