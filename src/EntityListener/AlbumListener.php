<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Album;
use App\Helper\VideoHelper;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, entity: Album::class)]
#[AsEntityListener(event: Events::prePersist, entity: Album::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Album::class)]
class AlbumListener
{
    private string $noCoverPath;

    public function __construct(AssetMapperInterface $assetMapper, private readonly VideoHelper $videoHelper, #[Autowire('%music_collection%')] $parameters)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($parameters['no_cover_path']);
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
