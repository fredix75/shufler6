<?php

namespace App\Entity\MusicCollection;

use App\Repository\MusicCollection\CloudTrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: CloudTrackRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('youtubeKey', message: 'Ce lien existe déja')]
class CloudTrack extends Piece
{

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('annee', new Assert\Range(
            notInRangeMessage: 'L\'année doit être comprise entre {{ min }} et {{ max }}.',
            min: 1000,
            max: (int)date('Y')
        ));
    }

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
