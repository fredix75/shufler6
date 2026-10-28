<?php

namespace App\EntityListener;

use App\Entity\Flux;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, entity: Flux::class)]
class FluxListener
{
    private string $noCoverPath;

    public function __construct(AssetMapperInterface $assetMapper, #[Autowire('%music_collection%')] array $parameters)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($parameters['no_cover_path']);
    }

    public function postLoad(Flux $flux): void
    {
        if (!$flux->getImage() && $flux->getType()->getName() === 'playlist') {
            $flux->setImage($this->noCoverPath);
        }
    }
}
