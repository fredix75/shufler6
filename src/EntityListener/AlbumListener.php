<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Album;
use App\Helper\VideoHelper;
use Symfony\Component\AssetMapper\AssetMapperInterface;

class AlbumListener
{
    private string $noCoverPath;

    private VideoHelper $videoHelper;

    public function __construct(array $parameters, AssetMapperInterface $assetMapper, VideoHelper $videoHelper)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($parameters['no_cover_path']);
        $this->videoHelper = $videoHelper;
    }

    public function postLoad(Album $album): void
    {
        if (!$album->getPicture()) {
            $album->setPicture($this->noCoverPath);
        }

    }

    public function prePersist(Album $album): void
    {
        if ($album->getPicture() === $this->noCoverPath) {
            $album->setPicture('');
        }

        $key = $album->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
        }
        $album->setYoutubeKey($key);
    }

    public function preUpdate(Album $album): void
    {
        if ($album->getPicture() === $this->noCoverPath) {
            $album->setPicture('');
        }

        $key = $album->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
        }
        $album->setYoutubeKey($key);
    }
}