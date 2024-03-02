<?php

namespace App\Entity;

use App\Repository\FluxMoodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxMoodRepository::class)]
class FluxMood
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'fluxMoods')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FluxType $type = null;

    #[ORM\OneToMany(targetEntity: Flux::class, mappedBy: 'mood')]
    private Collection $flux;

    public function __construct()
    {
        $this->flux = new ArrayCollection();
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

    public function getType(): ?FluxType
    {
        return $this->type;
    }

    public function setType(?FluxType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFlux(): Collection
    {
        return $this->flux;
    }

    public function addFlux(Flux $flux): static
    {
        if (!$this->flux->contains($flux)) {
            $this->flux->add($flux);
            $flux->setMoods($this);
        }

        return $this;
    }

    public function removeFlux(Flux $flux): static
    {
        if ($this->flux->removeElement($flux)) {
            // set the owning side to null (unless already changed)
            if ($flux->getMoods() === $this) {
                $flux->setMoods(null);
            }
        }

        return $this;
    }
}
