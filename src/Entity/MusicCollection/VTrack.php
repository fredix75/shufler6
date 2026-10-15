<?php

namespace App\Entity\MusicCollection;

use App\Repository\MusicCollection\VTrackRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VTrackRepository::class)]
class VTrack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $kind = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $youtubeKey = null;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $albumName = null;

    #[ORM\Column(nullable: true)]
    private ?int $albumYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $albumArtist = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $albumGenre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $albumPicture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $albumYoutubeKey = null;

    #[ORM\Column(length: 255)]
    private ?string $dataOrigin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(?string $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getAlbumName(): ?string
    {
        return $this->albumName;
    }

    public function setAlbumName(?string $albumName): static
    {
        $this->albumName = $albumName;

        return $this;
    }

    public function getAlbumYear(): ?int
    {
        return $this->albumYear;
    }

    public function setAlbumYear(?int $albumYear): static
    {
        $this->albumYear = $albumYear;

        return $this;
    }

    public function getAlbumArtist(): ?string
    {
        return $this->albumArtist;
    }

    public function setAlbumArtist(?string $albumArtist): static
    {
        $this->albumArtist = $albumArtist;

        return $this;
    }

    public function getAlbumGenre(): ?string
    {
        return $this->albumGenre;
    }

    public function setAlbumGenre(?string $albumGenre): static
    {
        $this->albumGenre = $albumGenre;

        return $this;
    }

    public function getAlbumPicture(): ?string
    {
        return $this->albumPicture;
    }

    public function setAlbumPicture(?string $albumPicture): static
    {
        $this->albumPicture = $albumPicture;

        return $this;
    }

    public function getAlbumYoutubeKey(): ?string
    {
        return $this->albumYoutubeKey;
    }

    public function setAlbumYoutubeKey(?string $albumYoutubeKey): static
    {
        $this->albumYoutubeKey = $albumYoutubeKey;

        return $this;
    }

    public function getDataOrigin(): ?string
    {
        return $this->dataOrigin;
    }

    public function setDataOrigin(string $dataOrigin): static
    {
        $this->dataOrigin = $dataOrigin;

        return $this;
    }
}
