<?php

namespace App\Entity;

use App\Contract\UploadInterface;
use App\Repository\ChannelFluxRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: ChannelFluxRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ChannelFlux implements UploadInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    private ?UploadedFile $file = null;

    private ?string $oldImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInsert = null;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: Flux::class)]
    private Collection $flux;

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

    #[ORM\PostLoad]
    public function setOldImage(): self
    {
        $this->oldImage = $this->image;

        return $this;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param UploadedFile|null $file
     */
    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    public function setProviderName(?string $providerName): self
    {
        $this->providerName = $providerName;

        return $this;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function setProviderId(?string $providerId): self
    {
        $this->providerId = $providerId;

        return $this;
    }

    public function getFlux(): Collection
    {
        return $this->flux;
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
