<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415223011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE picture_film (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, film_id INT NOT NULL, INDEX IDX_3DA43BB7567F5183 (film_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE picture_film ADD CONSTRAINT FK_3DA43BB7567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture_film DROP FOREIGN KEY FK_3DA43BB7567F5183');
        $this->addSql('DROP TABLE picture_film');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
