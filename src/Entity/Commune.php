<?php

namespace App\Entity;

use App\Repository\CommuneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommuneRepository::class)]
#[UniqueConstraint(columns: ['nom', 'cp'])]
class Commune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(length: 3)]
    private ?string $departementCode = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $siren = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $epci = null;

    #[ORM\Column(length: 3)]
    private ?string $regionCode = null;

    #[ORM\Column(length: 5)]
    private ?string $cp = null;

    #[ORM\Column]
    private ?int $population = null;

    #[ORM\Column]
    private array $coord = [];

    #[ORM\Column(length: 255)]
    private ?string $zone = null;

    #[ORM\Column]
    private ?float $surface = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDepartementCode(): ?string
    {
        return $this->departementCode;
    }

    public function setDepartementCode(string $departementCode): static
    {
        $this->departementCode = $departementCode;

        return $this;
    }

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(?string $siren): static
    {
        $this->siren = $siren;

        return $this;
    }

    public function getEpci(): ?string
    {
        return $this->epci;
    }

    public function setEpci(?string $epci): static
    {
        $this->epci = $epci;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(string $regionCode): static
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): static
    {
        $this->population = $population;

        return $this;
    }

    public function getCoord(): array
    {
        return $this->coord;
    }

    public function setCoord(array $coord): static
    {
        $this->coord = $coord;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(string $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(float $surface): static
    {
        $this->surface = $surface;

        return $this;
    }
}
