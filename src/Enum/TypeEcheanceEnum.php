<?php

namespace App\Enum;

enum TypeEcheanceEnum: string
{
    case Audience = 'Audience';
    case Delai_recours = 'Délai de recours';
    case Delai_conclusions = 'Délai de conclusions';
    case Depot_pieces = 'Dépôt de pièces';
    case Expertise = 'Expertise';
    case Autre = 'Autre';

    public function label(): string
    {
        return $this->value;
    }

    public function icon(): string
    {
        return match($this) {
            self::Audience => 'fa-gavel',
            self::Delai_recours => 'fa-clock',
            self::Delai_conclusions => 'fa-file-text',
            self::Depot_pieces => 'fa-folder',
            self::Expertise => 'fa-search',
            self::Autre => 'fa-calendar',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
