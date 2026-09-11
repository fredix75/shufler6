<?php

namespace App\Entity\MusicCollection;

use App\EntityListener\TrackListener;
use App\Repository\MusicCollection\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[EntityListeners([TrackListener::class])]
#[ORM\HasLifecycleCallbacks]
class Track extends Piece
{
    #[ORM\Column(nullable: true)]
    private ?int $numero = null;

    #[ORM\Column(length: 10)]
    private ?string $duree = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $bitrate = null;

    #[ORM\Column(nullable: true)]
    private ?float $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hash = null;

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getBitrate(): ?string
    {
        return $this->bitrate;
    }

    public function setBitrate(?string $bitrate): static
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function getExtraNote(): ?float
    {

        return $this->extraNote ?? $this->note;
    }

    public function doHash(): string
    {
        return hash('sha256', $this->numero.$this->titre.$this->auteur.$this->album.$this->artiste
            .$this->annee.$this->bitrate.$this->duree.$this->genre.$this->note.$this->pays);
    }

    public function onLoad(): void
    {
        $this->note = $this->extraNote > 0 && empty($this->note) || $this->extraNote < 0 && !empty($this->note) ? $this->extraNote : $this->note;
    }
}
