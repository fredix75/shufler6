<?php

namespace App\Entity\MusicCollection;

use App\Repository\MusicCollection\CloudAlbumRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CloudAlbumRepository::class)]
#[UniqueEntity('youtubeKey', message: 'Album already exists!')]
class CloudAlbum extends Album
{

}
