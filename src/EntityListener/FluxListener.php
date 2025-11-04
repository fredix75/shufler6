<?php

namespace App\EntityListener;

use App\Entity\Flux;
use Symfony\Component\AssetMapper\AssetMapperInterface;

class FluxListener
{
    private string $noCoverPath;

    public function __construct(array $parameters, AssetMapperInterface $assetMapper)
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
