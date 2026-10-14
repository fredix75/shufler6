<?php

namespace App\Twig\Components;

use App\Entity\MusicCollection\Piece;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PieceFrame
{
    private array $piece;

    public function getPiece(): array
    {
        return $this->piece;
    }

    public function setPiece(array $piece): void
    {
        $this->piece = $piece;
    }
}
