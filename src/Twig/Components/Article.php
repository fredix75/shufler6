<?php

namespace App\Twig\Components;

use App\Entity\Event;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Article
{
    private Event $event;

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(event $event): void
    {
        $this->event = $event;
    }


}
