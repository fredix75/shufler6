<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MoodRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MoodRepository::class)]
#[ApiResource(
  #  security: "is_granted('ROLE_USER')"
)]
class Mood
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Video::class, mappedBy: "moods")]
    private Collection $videos;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVideos(): Collection
    {
        return $this->videos;
    }
}
