<?php

namespace App\Enum;

enum StatutActeEnum: string
{
    case Brouillon  = 'Brouillon';
    case EnRevision = 'En révision';
    case Valide     = 'Validé';
    case Signe      = 'Signé';
    case Archive    = 'Archivé';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Brouillon  => 'secondary',
            self::EnRevision => 'warning',
            self::Valide     => 'info',
            self::Signe      => 'success',
            self::Archive    => 'dark',
        };
    }

    public function couleurDefaut(): string
    {
        return match ($this) {
            self::Brouillon  => '#9CA3AF',
            self::EnRevision => '#FDBA74',
            self::Valide     => '#22D3EE',
            self::Signe      => '#34D399',
            self::Archive    => '#6B7280',
        };
    }

    /** @return self[] */
    public function transitionsAutorisees(): array
    {
        return match ($this) {
            self::Brouillon  => [self::EnRevision, self::Valide],
            self::EnRevision => [self::Valide, self::Brouillon],
            self::Valide     => [self::Signe, self::EnRevision],
            self::Signe      => [self::Archive],
            self::Archive    => [],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
