<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout colonne matieres (json) dans cabinet_config pour gestion dynamique des matières';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS matieres json NOT NULL DEFAULT '[]'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cabinet_config DROP COLUMN IF EXISTS matieres');
    }
}
