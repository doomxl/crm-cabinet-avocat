<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add numero_dossier unique column to dossiers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dossiers ADD COLUMN IF NOT EXISTS numero_dossier VARCHAR(30) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_dossier_numero ON dossiers (numero_dossier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS uniq_dossier_numero');
        $this->addSql('ALTER TABLE dossiers DROP COLUMN IF EXISTS numero_dossier');
    }
}
