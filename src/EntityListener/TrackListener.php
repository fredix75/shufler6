<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Track;
use App\Helper\VideoHelper;

class TrackListener
{
    public function __construct(private readonly VideoHelper $videoHelper) {}

    public function prePersist(Track $track): void
    {
        $this->setHash($track);
    }

    public function preUpdate(Track $track): void
    {
        $this->setHash($track);
        $key = $track->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
        }
        $track->setYoutubeKey($key);
    }

    private function setHash(Track $track): void
    {
        $track->setHash($track->doHash());
    }

}