<?php

namespace App\Entity;

use App\Repository\CinemaPeopleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CinemaPeopleRepository::class)]
class CinemaPeople
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deathDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(nullable: true)]
    private ?float $popularity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(unique: true, nullable: true)]
    private ?int $tmdbId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $job = null;

    /**
     * @var Collection<int, FilmCasting>
     */
    #[ORM\OneToMany(targetEntity: FilmCasting::class, mappedBy: 'cinemaPeople')]
    private Collection $filmCastings;

    public function __construct()
    {
        $this->filmCastings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getDeathDate(): ?\DateTime
    {
        return $this->deathDate;
    }

    public function setDeathDate(?\DateTime $deathDate): static
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): static
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getPopularity(): ?float
    {
        return $this->popularity;
    }

    public function setPopularity(?float $popularity): static
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdbId;
    }

    public function setTmdbId(?int $tmdbId): static
    {
        $this->tmdbId = $tmdbId;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): static
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return Collection<int, FilmCasting>
     */
    public function getFilmCastings(): Collection
    {
        return $this->filmCastings;
    }

    public function addFilmCasting(FilmCasting $filmCasting): static
    {
        if (!$this->filmCastings->contains($filmCasting)) {
            $this->filmCastings->add($filmCasting);
            $filmCasting->setCinemaPeople($this);
        }

        return $this;
    }

    public function removeFilmCasting(FilmCasting $filmCasting): static
    {
        if ($this->filmCastings->removeElement($filmCasting)) {
            // set the owning side to null (unless already changed)
            if ($filmCasting->getCinemaPeople() === $this) {
                $filmCasting->setCinemaPeople(null);
            }
        }

        return $this;
    }
}
