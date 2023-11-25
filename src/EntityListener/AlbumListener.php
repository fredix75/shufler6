<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Album;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AlbumListener
{
    private string $noCoverPath;
    public function __construct(array $parameters)
    {
        $this->noCoverPath = $parameters['no_cover_path'];
    }

    public function postLoad(Album $album): void
    {
        if (!$album->getPicture()) {
            $album->setPicture($this->noCoverPath);
        }

    }

    public function preUpdate(Album $album): void
    {
        if ($album->getPicture() === $this->noCoverPath) {
            $album->setPicture('');
        }

    }
}