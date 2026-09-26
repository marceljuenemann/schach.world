<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260926134145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add confirmed column to registration_players table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration_players ADD confirmed TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration_players DROP COLUMN confirmed');
    }
}
