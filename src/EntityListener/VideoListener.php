<?php

namespace App\EntityListener;

use App\Entity\Video;
use App\Helper\VideoHelper;

class VideoListener
{
    public function __construct(private readonly VideoHelper $videoHelper){
    }

    public function postLoad(Video $video): void
    {
        $link = $video->getLien();
        if (strripos($link, 'youtube.com/watch?v=')) {
            $link = str_replace('watch?v=', 'embed/', $link);
        }
        $video->setLien(rtrim(preg_replace('/http:\/\//','https://', $link,1),'/'));
    }

    public function prePersist(Video $video): void
    {
        $this->sanitizeLink($video);
        if ($video->getAnnee()) {
            $periode = $this->videoHelper->selectPeriod($video->getAnnee());
            $video->setPeriode($periode);
        }
    }

    public function preUpdate(Video $video): void
    {
        $this->sanitizeLink($video);
        if ($video->getAnnee()) {
            $periode = $this->videoHelper->selectPeriod($video->getAnnee());
            $video->setPeriode($periode);
        }
    }

    private function sanitizeLink(Video $video): void
    {
        $lienOrigin = $video->getLien();

        switch($lienOrigin) {
            case false !== strripos($lienOrigin, 'youtube.com/watch?v='):
                $lien = str_replace('watch?v=', 'embed/', $lienOrigin);
                break;
            case false !== strripos($lienOrigin, 'https://youtu.be/'):
                $lien = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $lienOrigin);
                break;
            case false !== strripos($lienOrigin, '//vimeo.com/channels/staffpicks/'):
                $lien = str_replace('//vimeo.com/channels/staffpicks/', '//player.vimeo.com/video/', $lienOrigin);
                break;
            case false !== strripos($lienOrigin, '//vimeo.com/'):
                $lien = str_replace('//vimeo.com/', '//player.vimeo.com/video/', $lienOrigin);
                break;
            case false !== strripos($lienOrigin, 'dailymotion.com/video'):
                $lien = str_replace('dailymotion.com/video', 'dailymotion.com/embed/video', $lienOrigin);
                break;
            default:
                $lien = $lienOrigin;
        }

        $video->setLien($lien);
    }

}