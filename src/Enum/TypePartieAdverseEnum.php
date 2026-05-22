<?php

namespace App\Enum;

enum TypePartieAdverseEnum: string
{
    case Personne_physique = 'Personne physique';
    case Personne_morale = 'Personne morale';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
