<?php

namespace App\Entity\MusicCollection;

use App\EntityListener\TrackListener;
use App\Repository\MusicCollection\TrackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[EntityListeners([TrackListener::class])]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $auteur = null;

    #[ORM\Column(length: 255)]
    private ?string $album = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero = null;

    #[ORM\Column(nullable: true)]
    private ?string $annee = null;

    #[ORM\Column(length: 255)]
    private ?string $artiste = null;

    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 10)]
    private ?string $duree = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $bitrate = null;

    #[ORM\Column(nullable: true)]
    private ?float $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $youtubeKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hash = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isCheck = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(string $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(?string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getArtiste(): ?string
    {
        return $this->artiste;
    }

    public function setArtiste(string $artiste): self
    {
        $this->artiste = $artiste;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(string $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getBitrate(): ?string
    {
        return $this->bitrate;
    }

    public function setBitrate(?string $bitrate): self
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getYoutubeKey(): ?string
    {
        return $this->youtubeKey;
    }

    public function setYoutubeKey(?string $youtubeKey): self
    {
        $this->youtubeKey = $youtubeKey;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function doHash(): string
    {
        return hash('sha256', $this->numero.$this->titre.$this->auteur.$this->album.$this->artiste
            .$this->annee.$this->bitrate.$this->duree.$this->genre.$this->note.$this->pays);
    }

    public function getCheck(): ?bool
    {
        return $this->isCheck;
    }

    public function setCheck(?bool $isCheck = false): self
    {
        $this->isCheck = $isCheck;

        return $this;
    }
}
