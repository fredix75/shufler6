<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Track;
use App\Helper\VideoHelper;

class TrackListener
{
    public function __construct(private VideoHelper $videoHelper) {}

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