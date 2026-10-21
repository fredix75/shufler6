<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012121549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'View for Track Entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
<<<SQL
CREATE VIEW v_track AS
SELECT ROW_NUMBER() OVER() as id, id_track, author, title, year, kind, country, youtube_key, note,
       id_album, album_name, album_year, album_artist, album_genre, album_picture, album_youtube_key, data_origin
FROM (SELECT p.id as id_track,
    p.auteur      as author,
    p.titre       as title,
    p.annee       as year,
    p.genre       as kind,
    p.pays        as country,
    p.youtube_key as youtube_key,
    p.note,
    a.id          as id_album,
    a.name        as album_name,
    a.annee       as album_year,
    a.auteur      as album_artist,
    a.genre       as album_genre,
    a.picture     as album_picture,
    a.youtube_key as album_youtube_key,
    'piece'       as data_origin
FROM piece p
LEFT JOIN album a ON a.name = p.album and a.auteur = p.artiste
UNION
SELECT r.id as id_track,
    r.author      as author,
    r.name        as title,
    r.year        as annee,
    ''            as kind,
    ''            as country,
    r.youtube_key as youtube_key,
    ''            as note,
    null          as id_album,
    r.album       as album_name,
    r.year        as album_year,
    r.author      as album_artist,
    ''            as album_genre,
    r.picture_key as album_picture,
    ''            as album_youtube_key,
    'radial'      as data_origin
FROM radial r) AS vt
SQL
        );

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW v_track');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
