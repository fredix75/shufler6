<?php

namespace App\Twig\Components;

use App\Entity\Flux;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SquareFrame
{
    private Flux $playlist;

    public function getPlaylist(): Flux
    {
        return $this->playlist;
    }

    public function setPlaylist(Flux $playlist): void
    {
        $this->playlist = $playlist;
    }


}
