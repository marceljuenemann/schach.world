<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917163549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unregisteredAt column to registration_players table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration_players ADD unregistered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration_players DROP COLUMN unregistered_at');
    }
}
