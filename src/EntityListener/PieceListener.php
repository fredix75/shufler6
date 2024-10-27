<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Piece;
use App\Helper\VideoHelper;

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