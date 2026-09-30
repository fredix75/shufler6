<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260829080750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cinema_people (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, birth_date DATE DEFAULT NULL, death_date DATE DEFAULT NULL, birth_place VARCHAR(255) DEFAULT NULL, popularity DOUBLE PRECISION DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, tmdb_id INT DEFAULT NULL, job VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C1FE408C55BCC5E5 (tmdb_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film_casting (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(255) DEFAULT NULL, job VARCHAR(255) NOT NULL, cinema_people_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_31A5A1732412B70B (cinema_people_id), INDEX IDX_31A5A173567F5183 (film_id), UNIQUE INDEX UNIQ_31A5A173567F51832412B70BFBD8E0F8 (film_id, cinema_people_id, job), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE film_casting ADD CONSTRAINT FK_31A5A1732412B70B FOREIGN KEY (cinema_people_id) REFERENCES cinema_people (id)');
        $this->addSql('ALTER TABLE film_casting ADD CONSTRAINT FK_31A5A173567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film_casting DROP FOREIGN KEY FK_31A5A1732412B70B');
        $this->addSql('ALTER TABLE film_casting DROP FOREIGN KEY FK_31A5A173567F5183');
        $this->addSql('DROP TABLE cinema_people');
        $this->addSql('DROP TABLE film_casting');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
