<?php

namespace App\EntityListener;

use App\Entity\Flux;
use App\Repository\FluxTypeRepository;
use Symfony\Component\AssetMapper\AssetMapperInterface;

class FluxListener
{
    private string $noCoverPath;

    public function __construct(array $parameters, AssetMapperInterface $assetMapper, private readonly FluxTypeRepository $fluxTypeRepository)
    {
        $this->noCoverPath = $assetMapper->getPublicPath($parameters['no_cover_path']);
    }

    public function postLoad(Flux $flux): void
    {
        if (!$flux->getImage() && $flux->getType() === $this->fluxTypeRepository->findOneBy(['name' => 'playlist'])) {
            $flux->setImage($this->noCoverPath);
        }
    }
}