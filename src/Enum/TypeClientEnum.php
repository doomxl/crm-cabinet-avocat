<?php

namespace App\Enum;

enum TypeClientEnum: string
{
    case Particulier = 'Particulier';
    case Professionnel = 'Professionnel';
    case Entreprise = 'Entreprise';

    public function label(): string
    {
        return match($this) {
            self::Particulier => 'Particulier',
            self::Professionnel => 'Professionnel',
            self::Entreprise => 'Entreprise',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
