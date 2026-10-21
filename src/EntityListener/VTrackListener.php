<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\VTrack;

class VTrackListener
{
    public function __construct(private readonly array $parameters)
    {}

    public function postLoad(VTrack $track): void
    {
        if ($track->getDataOrigin() === 'radial' && $track->getAlbumPicture()) {
            $track->setAlbumPicture($this->parameters['picture_url'].$track->getAlbumPicture().'/120x120');
        }
    }
}
