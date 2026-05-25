<?php

namespace App\Enum;

enum MatiereEnum: string
{
    case Droit_familial = 'Droit familial';
    case Droit_penal = 'Droit pénal';
    case Droit_des_affaires = 'Droit des affaires';
    case Droit_social = 'Droit social';
    case Droit_immobilier = 'Droit immobilier';
    case Droit_administratif = 'Droit administratif';
    case Droit_international = 'Droit international';
    case Autres = 'Autres';

    public function label(): string
    {
        return $this->value;
    }

    public function couleur(): string
    {
        return match($this) {
            self::Droit_familial      => '#F472B6',
            self::Droit_penal         => '#F87171',
            self::Droit_des_affaires  => '#60A5FA',
            self::Droit_social        => '#FDBA74',
            self::Droit_immobilier    => '#34D399',
            self::Droit_administratif => '#A78BFA',
            self::Droit_international => '#22D3EE',
            self::Autres              => '#9CA3AF',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
