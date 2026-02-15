<?php

namespace App\Twig\Components;

use App\Entity\Video;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class VideoFrame
{
    private Video $video;

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function setVideo(Video $video): void
    {
        $this->video = $video;
    }
}
