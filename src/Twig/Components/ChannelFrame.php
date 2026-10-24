<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ChannelFrame
{
    private array $channel;

    public function getChannel(): array
    {
        return $this->channel;
    }

    public function setChannel(array $channel): void
    {
        $this->channel = $channel;
    }

}
