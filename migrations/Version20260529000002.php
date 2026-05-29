<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add couleurs_echeance JSON column to cabinet_config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS couleurs_echeance json NOT NULL DEFAULT '{}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cabinet_config DROP COLUMN IF EXISTS couleurs_echeance');
    }
}
