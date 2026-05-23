<?php

namespace App\Enum;

enum CiviliteEnum: string
{
    case M   = 'M.';
    case Mme = 'Mme';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
