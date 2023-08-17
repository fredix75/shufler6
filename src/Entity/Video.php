<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\VideoListener;
use App\Repository\VideoRepository;
use App\Validator\VideoValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[Assert\Callback([VideoValidator::class, 'validate'])]
#[ORM\HasLifecycleCallbacks]
#[EntityListeners([VideoListener::class])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/videos',
            schemes: ['https'],
            paginationItemsPerPage: 25,
            normalizationContext: ['groups' => 'video:list']
        ),
        new Get(
            uriTemplate: '/video/{id}',
            requirements: ['id' => '\d+'],
            schemes: ['https'],
        ),
        new Post(
            uriTemplate: '/video',
            status: 301,
            security: "is_granted('ROLE_AUTEUR')"
        ),
        new Put(
            uriTemplate: '/video/{id}',
            requirements: ['id' => '\d+'],
            security: "is_granted('ROLE_AUTEUR')"
        ),
        new Patch(
            uriTemplate: '/video/{id}',
            requirements: ['id' => '\d+'],
            security: "is_granted('ROLE_AUTEUR')"
        ),
        new Delete(
            uriTemplate: '/video/{id}',
            requirements: ['id' => '\d+'],
            security: "is_granted('ROLE_ADMIN')"
        )
],
    security: "is_granted('ROLE_USER')")]
#[ApiFilter(SearchFilter::class, properties: ['categorie' => 'exact', 'genre' => 'exact'])]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["video:list"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["video:list", "video:write"])]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Groups(["video:list"])]
    private ?string $auteur = null;

    #[ORM\Column(length: 255)]
    #[Groups(["video:list"])]
    private ?string $lien = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chapo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["video:list"])]
    private ?int $annee = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(["video:list"])]
    private ?int $categorie = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(["video:list"])]
    private ?int $genre = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(["video:list"])]
    private ?int $priorite = null;

    #[ORM\Column(length: 9)]
    #[Groups(["video:list"])]
    private ?string $periode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["video:list"])]
    private ?\DateTimeInterface $dateInsert = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(["video:list"])]
    private ?bool $published = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateUpdate = null;

    #[ORM\ManyToMany(targetEntity: Mood::class, inversedBy: 'videos')]
    private Collection $moods;

    public function __construct()
    {
        $this->moods = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }

    public function getChapo(): ?string
    {
        return $this->chapo;
    }

    public function setChapo(?string $chapo): self
    {
        $this->chapo = $chapo;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(?int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getCategorie(): ?int
    {
        return $this->categorie;
    }

    public function setCategorie(int $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getGenre(): ?int
    {
        return $this->genre;
    }

    public function setGenre(?int $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getPriorite(): ?int
    {
        return $this->priorite;
    }

    public function setPriorite(int $priorite): self
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(string $periode): self
    {
        $this->periode = $periode;

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

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    #[ORM\PreUpdate]
    public function setDateUpdate(): self
    {
        $this->dateUpdate = new \DateTime();

        return $this;
    }

    public function getMoods(): Collection
    {
        return $this->moods;
    }

    public function addMood(Mood $mood): self
    {
        if (!$this->moods->contains($mood)) {
            $this->moods->add($mood);
        }

        return $this;
    }

    public function removeMood(Mood $mood): self
    {
        $this->moods->removeElement($mood);

        return $this;
    }
}
