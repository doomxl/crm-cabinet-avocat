<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout couleurs_statut_facture JSONB sur cabinet_config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS couleurs_statut_facture JSONB NOT NULL DEFAULT '{}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config DROP COLUMN IF EXISTS couleurs_statut_facture");
    }
}
