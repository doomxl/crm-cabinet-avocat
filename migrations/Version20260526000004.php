<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Migration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout couleurs_conflit JSONB sur cabinet_config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config ADD COLUMN IF NOT EXISTS couleurs_conflit JSONB NOT NULL DEFAULT '{}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE cabinet_config DROP COLUMN IF EXISTS couleurs_conflit");
    }
}
