<?php

namespace App\Twig\Components;

use App\Entity\MusicCollection\Album;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class AlbumFrame
{
    private Album $album;

    public function getAlbum(): Album
    {
        return $this->album;
    }

    public function setAlbum(Album $album): void
    {
        $this->album = $album;
    }



}
