<?php

namespace App\Entity;

use App\Repository\FluxMoodRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxMoodRepository::class)]
class FluxMood
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'fluxMoods')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FluxType $type = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getType(): ?FluxType
    {
        return $this->type;
    }

    public function setType(?FluxType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
