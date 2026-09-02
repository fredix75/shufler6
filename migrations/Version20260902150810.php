<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260902150810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commune (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, departement_code VARCHAR(3) NOT NULL, siren VARCHAR(15) DEFAULT NULL, epci VARCHAR(15) DEFAULT NULL, region_code VARCHAR(3) NOT NULL, cp VARCHAR(5) NOT NULL, population INT NOT NULL, coord JSON NOT NULL, zone VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commune');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
