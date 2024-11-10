<?php

namespace App\Entity;

use App\Repository\FilterPieceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilterPieceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FilterPiece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private array $genres = [];

    private array $simpleGenres = [];

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

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function setGenres(array $genres): static
    {
        $this->genres = $genres;

        return $this;
    }

    public function getSimpleGenres(): array
    {
        return $this->simpleGenres;
    }

    #[ORM\PostLoad]
    public function setSimpleGenres(): static
    {
        $this->simpleGenres = array_values($this->genres);

        return $this;
    }
}
