<?php

namespace App\Entity;

use App\Repository\FilmCastingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmCastingRepository::class)]
#[ORM\UniqueConstraint(columns: ['film_id', 'cinema_people_id'])]
class FilmCasting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $job = null;

    #[ORM\ManyToOne(inversedBy: 'filmCastings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CinemaPeople $cinemaPeople = null;

    #[ORM\ManyToOne(inversedBy: 'filmCastings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Film $film = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): static
    {
        $this->job = $job;

        return $this;
    }

    public function getCinemaPeople(): ?CinemaPeople
    {
        return $this->cinemaPeople;
    }

    public function setCinemaPeople(?CinemaPeople $cinemaPeople): static
    {
        $this->cinemaPeople = $cinemaPeople;

        return $this;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): static
    {
        $this->film = $film;

        return $this;
    }
}
