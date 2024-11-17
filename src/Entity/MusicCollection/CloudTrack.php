<?php

namespace App\Entity\MusicCollection;

use App\Repository\MusicCollection\CloudTrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CloudTrackRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('youtubeKey', message: 'Ce lien existe déja')]
class CloudTrack extends Piece
{
    public function getNote(): ?float {
        return $this->extraNote;
    }

    #[ORM\PrePersist]
    public function setDefaultValues(): static
    {
        $this->artiste = $this->auteur;
        $this->album = 'VRAC';

        return $this;
    }

}
