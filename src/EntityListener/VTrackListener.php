<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\VTrack;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, entity: VTrack::class)]
final readonly class VTrackListener
{
    private string $noCoverPath;

    public function __construct(AssetMapperInterface $assetMapper, #[Autowire('%radial%')] private array $radialParameters, #[Autowire('%music_collection%')] $musicParameters)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($musicParameters['no_cover_path']);
    }

    public function postLoad(VTrack $track): void
    {
        if ($track->getDataOrigin() === 'radial' && $track->getAlbumPicture()) {
            $track->setAlbumPicture(sprintf('%s/%s/%s', $this->parameters['picture_url'], $track->getAlbumPicture(), '120x120'));
        } else if ($track->getDataOrigin() === 'piece' && !$track->getAlbumPicture()) {
            $track->setAlbumPicture($this->noCoverPath);
        }
    }
}
