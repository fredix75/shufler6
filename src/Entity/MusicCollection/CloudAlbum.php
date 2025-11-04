<?php

namespace App\Entity\MusicCollection;

use App\Repository\MusicCollection\CloudAlbumRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: CloudAlbumRepository::class)]
#[UniqueEntity('youtubeKey', message: 'Album already exists!')]
class CloudAlbum extends Album
{
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('annee', new Assert\Range(
            notInRangeMessage: 'L\'année doit être comprise entre {{ min }} et {{ max }}.',
            min: 1000,
            max: (int)date('Y')
        ));
    }
}
