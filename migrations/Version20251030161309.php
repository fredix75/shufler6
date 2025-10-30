<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030161309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL, CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('CREATE INDEX IDX_39986E4355AB1405E237E06 ON album (auteur, name)');
        $this->addSql('ALTER TABLE filter_piece CHANGE genres genres JSON NOT NULL');
        $this->addSql('CREATE INDEX IDX_44CA0B2355AB14039986E43 ON piece (auteur, album)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_39986E4355AB1405E237E06 ON album');
        $this->addSql('ALTER TABLE filter_piece CHANGE genres genres JSON NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('DROP INDEX IDX_44CA0B2355AB14039986E43 ON piece');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
