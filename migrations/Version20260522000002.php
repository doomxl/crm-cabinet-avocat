<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 4 — Workflow actes : ajout colonne statut sur actes_generes
 */
final class Version20260522000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 4 workflow : colonne statut sur actes_generes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE actes_generes ADD COLUMN IF NOT EXISTS statut VARCHAR(20) NOT NULL DEFAULT 'Brouillon'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE actes_generes DROP COLUMN IF EXISTS statut");
    }
}
