<?php

namespace App\Twig\Components;

use App\Entity\MusicCollection\Piece;
use App\Entity\MusicCollection\VTrack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PieceFrame
{
    private VTrack $piece;

    public function getPiece(): VTrack
    {
        return $this->piece;
    }

    public function setPiece(VTrack $piece): void
    {
        $this->piece = $piece;
    }
}
