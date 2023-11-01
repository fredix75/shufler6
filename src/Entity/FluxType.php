<?php

namespace App\Entity;

use App\Repository\FluxTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxTypeRepository::class)]
class FluxType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: FluxMood::class, orphanRemoval: true)]
    private Collection $fluxMoods;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Flux::class)]
    private Collection $flux;

    public function __construct()
    {
        $this->fluxMoods = new ArrayCollection();
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

    /**
     * @return Collection<int, FluxMood>
     */
    public function getFluxMoods(): Collection
    {
        return $this->fluxMoods;
    }

    public function addFluxMood(FluxMood $fluxMood): static
    {
        if (!$this->fluxMoods->contains($fluxMood)) {
            $this->fluxMoods->add($fluxMood);
            $fluxMood->setType($this);
        }

        return $this;
    }

    public function removeFluxMood(FluxMood $fluxMood): static
    {
        if ($this->fluxMoods->removeElement($fluxMood)) {
            // set the owning side to null (unless already changed)
            if ($fluxMood->getType() === $this) {
                $fluxMood->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flux>
     */
    public function getFlux(): Collection
    {
        return $this->flux;
    }

    public function addFlux(Flux $flux): static
    {
        if (!$this->flux->contains($flux)) {
            $this->flux->add($flux);
            $flux->setType($this);
        }

        return $this;
    }

    public function removeFlux(Flux $flux): static
    {
        if ($this->flux->removeElement($flux)) {
            // set the owning side to null (unless already changed)
            if ($flux->getType() === $this) {
                $flux->setType(null);
            }
        }

        return $this;
    }
}
