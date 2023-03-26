<?php

namespace App\Entity;

use App\Repository\FluxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxRepository::class)]
class Flux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    private ?string $oldImage = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $mood = null;

    #[ORM\ManyToOne]
    private ?ChannelFlux $channel = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInsert = null;

    private ?string $contenu = null;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getOldImage(): ?string
    {
        return $this->oldImage;
    }

    public function setOldImage(string $oldImage): self
    {
        $this->oldImage = $oldImage;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMood(): ?int
    {
        return $this->mood;
    }

    public function setMood(?int $mood): self
    {
        $this->mood = $mood;

        return $this;
    }

    public function getChannel(): ?ChannelFlux
    {
        return $this->channel;
    }

    public function setChannel(?ChannelFlux $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getDateInsert(): ?\DateTimeInterface
    {
        return $this->dateInsert;
    }

    public function setDateInsert(\DateTimeInterface $dateInsert): self
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }
}
