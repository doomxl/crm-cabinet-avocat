<?php

namespace App\Enum;

enum TypeDocumentEnum: string
{
    case Note_honoraires = "Note d'honoraires";
    case Facture = 'Facture';
    case Avoir = 'Avoir';
    case Provision = 'Provision';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
