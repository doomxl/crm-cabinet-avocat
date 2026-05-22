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
            self::Droit_familial => '#EC4899',
            self::Droit_penal => '#EF4444',
            self::Droit_des_affaires => '#1E40AF',
            self::Droit_social => '#F97316',
            self::Droit_immobilier => '#059669',
            self::Droit_administratif => '#7C3AED',
            self::Droit_international => '#06B6D4',
            self::Autres => '#6B7280',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
