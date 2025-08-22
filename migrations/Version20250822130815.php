<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822130815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add additionalFields JSON column to registration_players table';
    }

    public function up(Schema $schema): void
    {
        // Add additionalFields JSON column to registration_players table
        $this->addSql('ALTER TABLE registration_players ADD additional_fields JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove additionalFields column from registration_players table
        $this->addSql('ALTER TABLE registration_players DROP COLUMN additional_fields');
    }
}
