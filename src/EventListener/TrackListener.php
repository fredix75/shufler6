<?php

namespace App\EventListener;

use App\Entity\MusicCollection\Track;

class TrackListener
{

    public function prePersist(Track $track): void
    {
        $this->doHash($track);
    }

    public function preUpdate(Track $track): void
    {
        $this->doHash($track);
    }

    private function doHash(Track $track)
    {
        $track->setHash(hash('sha256', $track->stringify()));
    }

}