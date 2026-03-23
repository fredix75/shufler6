<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307120316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE painter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, birth_year INT DEFAULT NULL, death_year INT DEFAULT NULL, bio LONGTEXT DEFAULT NULL, picture LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE painting (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, file VARCHAR(255) NOT NULL, painter_id INT NOT NULL, INDEX IDX_66B9EBA0D3A137FE (painter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE painting ADD CONSTRAINT FK_66B9EBA0D3A137FE FOREIGN KEY (painter_id) REFERENCES painter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE painting DROP FOREIGN KEY FK_66B9EBA0D3A137FE');
        $this->addSql('DROP TABLE painter');
        $this->addSql('DROP TABLE painting');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
