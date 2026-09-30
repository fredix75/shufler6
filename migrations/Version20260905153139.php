<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260905153139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commune ADD arrdt_from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EEAEC960CC FOREIGN KEY (arrdt_from_id) REFERENCES commune (id)');
        $this->addSql('CREATE INDEX IDX_E2E2D1EEAEC960CC ON commune (arrdt_from_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commune DROP FOREIGN KEY FK_E2E2D1EEAEC960CC');
        $this->addSql('DROP INDEX IDX_E2E2D1EEAEC960CC ON commune');
        $this->addSql('ALTER TABLE commune DROP arrdt_from_id');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
