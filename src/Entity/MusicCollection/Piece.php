<?php

namespace App\Entity\MusicCollection;

use App\EntityListener\PieceListener;
use App\Repository\MusicCollection\PieceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\EntityListeners;
use Doctrine\ORM\Mapping\InheritanceType;

#[ORM\Entity(repositoryClass: PieceRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'data_type', type: 'integer')]
#[DiscriminatorMap([1 => Track::class, 2 => CloudTrack::class])]
#[EntityListeners([PieceListener::class])]
abstract class Piece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $titre = null;

    #[ORM\Column(length: 255)]
    protected ?string $auteur = null;

    #[ORM\Column(length: 255)]
    protected ?string $album = null;

    #[ORM\Column(nullable: true)]
    protected ?string $annee = null;

    #[ORM\Column(length: 255)]
    protected ?string $artiste = null;

    #[ORM\Column(length: 255)]
    protected ?string $genre = null;

    #[ORM\Column(length: 3, nullable: true)]
    protected ?string $pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $youtubeKey = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $isCheck = false;

    #[ORM\Column(nullable: true)]
    protected ?float $extraNote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(?string $album): static

    {
        $this->album = $album;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(?string $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getArtiste(): ?string
    {
        return $this->artiste;
    }

    public function setArtiste(?string $artiste): static
    {
        $this->artiste = $artiste;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getYoutubeKey(): ?string
    {
        return $this->youtubeKey;
    }

    public function setYoutubeKey(?string $youtubeKey): static
    {
        $this->youtubeKey = $youtubeKey;

        return $this;
    }

    public function getIsCheck(): ?bool
    {
        return $this->isCheck;
    }

    public function setIsCheck(?bool $isCheck): static
    {
        $this->isCheck = $isCheck;

        return $this;
    }

    public function getExtraNote(): ?float
    {

        return $this->extraNote;
    }

    public function setExtraNote(?float $extraNote): static
    {
        $this->extraNote = $extraNote;

        return $this;
    }
}
