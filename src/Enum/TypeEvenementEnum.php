<?php

namespace App\Enum;

enum TypeEvenementEnum: string
{
    case note = 'note';
    case appel = 'appel';
    case email = 'email';
    case courrier = 'courrier';
    case audience = 'audience';
    case reunion = 'reunion';
    case acte = 'acte';
    case decision = 'decision';
    case document = 'document';
    case facturation = 'facturation';
    case systeme = 'systeme';
    case autre = 'autre';

    public function label(): string
    {
        return match($this) {
            self::note => 'Note',
            self::appel => 'Appel téléphonique',
            self::email => 'Email',
            self::courrier => 'Courrier',
            self::audience => 'Audience',
            self::reunion => 'Réunion',
            self::acte => 'Acte',
            self::decision => 'Décision',
            self::document => 'Document',
            self::facturation => 'Facturation',
            self::systeme => 'Système',
            self::autre => 'Autre',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::note => 'fa-sticky-note',
            self::appel => 'fa-phone',
            self::email => 'fa-envelope',
            self::courrier => 'fa-mail-bulk',
            self::audience => 'fa-gavel',
            self::reunion => 'fa-users',
            self::acte => 'fa-file-contract',
            self::decision => 'fa-check-circle',
            self::document => 'fa-file',
            self::facturation => 'fa-file-invoice-dollar',
            self::systeme => 'fa-cog',
            self::autre => 'fa-circle',
        };
    }

    public function couleur(): string
    {
        return match($this) {
            self::note => '#6B7280',
            self::appel => '#3B82F6',
            self::email => '#8B5CF6',
            self::courrier => '#F59E0B',
            self::audience => '#EF4444',
            self::reunion => '#10B981',
            self::acte => '#1E40AF',
            self::decision => '#059669',
            self::document => '#6366F1',
            self::facturation => '#F97316',
            self::systeme => '#9CA3AF',
            self::autre => '#6B7280',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
