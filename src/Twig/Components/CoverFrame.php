<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class CoverFrame
{
    private ?int $idAlbum = null;

    private array $cover;

    public function getIdAlbum(): ?int
    {
        return $this->idAlbum;
    }

    public function setIdAlbum(?int $idAlbum): void
    {
        $this->idAlbum = $idAlbum;
    }

    public function getCover(): array
    {
        return $this->cover;
    }

    public function setCover(array $cover): void
    {
        $this->cover = $cover;
    }
}
