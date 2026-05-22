<?php

namespace App\Enum;

enum ModeReglementEnum: string
{
    case Virement = 'Virement';
    case Cheque = 'Chèque';
    case Especes = 'Espèces';
    case Carte_bancaire = 'Carte bancaire';
    case Prelevement = 'Prélèvement';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
