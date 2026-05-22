<?php

namespace App\Enum;

enum CategorieEcritureEnum: string
{
    case AA = 'AA';
    case AB = 'AB';
    case AC = 'AC';
    case AD = 'AD';
    case BA = 'BA';
    case BB = 'BB';
    case BC = 'BC';
    case BD = 'BD';
    case BE = 'BE';
    case BF = 'BF';
    case BG = 'BG';
    case BH = 'BH';
    case BJ = 'BJ';
    case BK = 'BK';
    case BL = 'BL';
    case BM = 'BM';
    case BN = 'BN';
    case BP = 'BP';
    case BQ = 'BQ';
    case BR = 'BR';
    case BS = 'BS';
    case BT = 'BT';
    case BV = 'BV';

    public function label(): string
    {
        return match($this) {
            self::AA => 'AA - Honoraires',
            self::AB => 'AB - Remboursements de frais',
            self::AC => 'AC - Produits financiers',
            self::AD => 'AD - Autres recettes',
            self::BA => 'BA - Achats',
            self::BB => 'BB - Variation de stock',
            self::BC => 'BC - Personnel extérieur',
            self::BD => 'BD - Impôts et taxes',
            self::BE => 'BE - Travaux, fournitures, services',
            self::BF => 'BF - Loyers et charges locatives',
            self::BG => 'BG - Locations de matériels',
            self::BH => 'BH - Entretien et réparations',
            self::BJ => 'BJ - Personnel salarié - salaires',
            self::BK => 'BK - Personnel salarié - charges',
            self::BL => 'BL - Frais de véhicules',
            self::BM => 'BM - Frais de déplacements',
            self::BN => 'BN - Frais de réception',
            self::BP => 'BP - Frais de bureau et de documentation',
            self::BQ => 'BQ - Frais financiers',
            self::BR => 'BR - Primes d\'assurance',
            self::BS => 'BS - Déductions diverses',
            self::BT => 'BT - Amortissements',
            self::BV => 'BV - Divers à déduire',
        };
    }

    public function isRecette(): bool
    {
        return in_array($this, [self::AA, self::AB, self::AC, self::AD]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
