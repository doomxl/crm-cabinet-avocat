<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout colonne contexte sur verifications_conflit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE verifications_conflit ADD COLUMN IF NOT EXISTS contexte VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE verifications_conflit DROP COLUMN IF EXISTS contexte");
    }
}
