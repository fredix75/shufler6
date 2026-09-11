<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class HorizontalCard
{
    private string $title;

    private ?string $path = null;

    private ?string $pictureSrc = null;


    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getPictureSrc(): ?string
    {
        return $this->pictureSrc;
    }

    public function setPictureSrc(?string $pictureSrc): void
    {
        $this->pictureSrc = $pictureSrc;
    }

}
