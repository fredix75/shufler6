<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\VTrack;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, entity: VTrack::class)]
final readonly class VTrackListener
{
    public function __construct(#[Autowire('%radial%')] private array $parameters)
    {}

    public function postLoad(VTrack $track): void
    {
        if ($track->getDataOrigin() === 'radial' && $track->getAlbumPicture()) {
            $track->setAlbumPicture(sprintf('%s/%s/%s', $this->parameters['picture_url'], $track->getAlbumPicture(), '120x120'));
        }
    }
}
