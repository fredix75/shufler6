<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use App\Validator\VideoValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[Assert\Callback([VideoValidator::class, 'validate'])]
#[ORM\HasLifecycleCallbacks]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $auteur = null;

    #[ORM\Column(length: 255)]
    private ?string $lien = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chapo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte = null;

    #[ORM\Column(nullable: true)]
    private ?int $annee = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $categorie = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $genre = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $priorite = null;

    #[ORM\Column(length: 9)]
    private ?string $periode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_insert = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $published = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_update = null;

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
        return $this->date_insert;
    }

    #[ORM\PrePersist]
    public function setDateInsert(): self
    {
        $this->date_insert = new \DateTime();

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
        return $this->date_update;
    }

    #[ORM\PreUpdate]
    public function setDateUpdate(): self
    {
        $this->date_update = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, Mood>
     */
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
