<?php

namespace App\Entity;

use App\Contract\UploadInterface;
use App\Repository\FluxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: FluxRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('url', message: 'Cet URL est déja utilisée')]
class Flux implements UploadInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    private ?UploadedFile $file = null;

    private ?string $oldImage = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $mood = null;

    #[ORM\ManyToOne(inversedBy:"flux")]
    private ?ChannelFlux $channel = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInsert = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
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

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getOldImage(): ?string
    {
        return $this->oldImage;
    }

    #[ORM\PostLoad]
    public function setOldImage(): self
    {
        $this->oldImage = $this->image;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
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

    #[ORM\PrePersist]
    public function setDateInsert(): self
    {
        $this->dateInsert = new \DateTime();

        return $this;
    }
}
