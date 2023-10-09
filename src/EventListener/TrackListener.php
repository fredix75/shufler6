<?php

namespace App\EventListener;

use App\Entity\MusicCollection\Track;

class TrackListener
{

    public function prePersist(Track $track): void
    {
        $this->setHash($track);
    }

    public function preUpdate(Track $track): void
    {
        $this->setHash($track);
    }

    private function setHash(Track $track): void
    {
        $track->setHash($track->doHash());
    }

}