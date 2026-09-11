<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328151245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film ADD overview LONGTEXT DEFAULT NULL, ADD original_language VARCHAR(5) DEFAULT NULL, ADD original_title VARCHAR(255) DEFAULT NULL, ADD tmdb_id INT DEFAULT NULL, ADD poster_path VARCHAR(255) DEFAULT NULL, ADD backdrop_path VARCHAR(255) DEFAULT NULL, ADD popularity DOUBLE PRECISION DEFAULT NULL, ADD genres JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film DROP overview, DROP original_language, DROP original_title, DROP tmdb_id, DROP poster_path, DROP backdrop_path, DROP popularity, DROP genres');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
