<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Album;
use Symfony\Component\AssetMapper\AssetMapperInterface;

class AlbumListener
{
    private string $noCoverPath;
    public function __construct(array $parameters, AssetMapperInterface $assetMapper)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($parameters['no_cover_path']);
    }

    public function postLoad(Album $album): void
    {
        if (!$album->getPicture()) {
            $album->setPicture($this->noCoverPath);
        }

    }

    public function preUpdate(Album $album): void
    {
        if ($album->getPicture() === $this->noCoverPath) {
            $album->setPicture('');
        }

    }
}