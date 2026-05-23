<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 2 — Actes & Modèles
 * - Ajout civilite sur clients (M. / Mme)
 * - Ajout avocat_nom, avocat_barreau, avocat_numero sur cabinet_config
 */
final class Version20260522000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 2 actes : civilité client + informations avocat cabinet';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE clients ADD COLUMN IF NOT EXISTS civilite VARCHAR(5) NULL");

        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS avocat_nom VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS avocat_barreau VARCHAR(100) NULL");
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS avocat_numero VARCHAR(50) NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE clients DROP COLUMN IF EXISTS civilite");

        $this->addSql("ALTER TABLE cabinet_config DROP COLUMN IF EXISTS avocat_nom");
        $this->addSql("ALTER TABLE cabinet_config DROP COLUMN IF EXISTS avocat_barreau");
        $this->addSql("ALTER TABLE cabinet_config DROP COLUMN IF EXISTS avocat_numero");
    }
}
