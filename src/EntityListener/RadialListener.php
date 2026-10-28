<?php

namespace App\EntityListener;

use App\Entity\Radial;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, entity: Radial::class)]
final class RadialListener
{

    public function __construct(
        #[Autowire('%radial%')] private array $params
    ) {}

    public function postLoad(Radial $radial): void
    {
        $radial->setAlbumPicture(sprintf('%s/%s/%s',$this->params['picture_url'], $radial->getPictureKey(), '120x120'));
    }
}
