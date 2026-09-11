<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    private array $flashes;

    public function getFlashes(): array
    {
        return $this->flashes;
    }

    public function setFlashes(array $flashes): void
    {
        $this->flashes = $flashes;
    }
}
