<?php

namespace App\EventListener;

use App\Entity\Video;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class VideoListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Video) {
            return;
        }

        $this->sanitizeLink($entity);

    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Video) {
            return;
        }

        $this->sanitizeLink($entity);
    }

    private function sanitizeLink($entity): void
    {
        $lienOrigin = $entity->getLien();

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

        $entity->setLien($lien);
    }

}