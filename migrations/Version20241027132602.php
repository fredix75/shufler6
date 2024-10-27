<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027132602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track RENAME piece');
        $this->addSql('ALTER TABLE piece ADD extra_note DOUBLE PRECISION DEFAULT NULL, ADD data_type INT NOT NULL, CHANGE duree duree VARCHAR(10) DEFAULT NULL');
        $this->addSql('UPDATE piece SET data_type = 1 WHERE TRUE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piece DROP extra_note, DROP data_type, CHANGE duree duree VARCHAR(10) NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
