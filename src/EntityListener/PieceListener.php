<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Piece;
use App\Helper\VideoHelper;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Piece::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Piece::class)]
class PieceListener
{
    public function __construct(private readonly VideoHelper $videoHelper)
    {
    }

    public function prePersist(Piece $piece): void
    {
        $key = $piece->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
            $piece->setYoutubeKey($key);
        }
    }

    public function preUpdate(Piece $piece): void
    {
        $key = $piece->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
            $piece->setYoutubeKey($key);
        }
    }

}
