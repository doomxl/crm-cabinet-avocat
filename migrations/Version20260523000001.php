<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Perf chronologie : index composite (dossier_id, date DESC) couvrant le ORDER BY
 */
final class Version20260523000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index composite chronologie(dossier_id, date DESC) pour couvrir le ORDER BY';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_chronologie_dossier_date ON chronologie (dossier_id, date DESC)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_chronologie_dossier_date');
    }
}
