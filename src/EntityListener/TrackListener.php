<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Track;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Track::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Track::class)]
class TrackListener
{
    public function __construct() {}

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
