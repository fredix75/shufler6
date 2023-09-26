<?php

namespace App\Helper;

class VideoHelper
{
    const YOUTUBE = 'youtube';
    const YOUTUBE_API = 'https://img.youtube.com/vi/';
    const YOUTUBE_WATCH = 'https://www.youtube.com/watch?v=';

    /**
     * *********************************
     */
    const DAILYMOTION = 'dailymotion';
    const DAILYMOTION_EMBED = '//dailymotion.com/embed/video';
    const DAILYMOTION_API = 'https://dailymotion.com/services/oembed?url=';

    /**
     * *********************************
     */
    const VIMEO = 'vimeo';
    const VIMEO_API = 'https://vimeo.com/api/v2/video/';

    public function getPlatform(string $lien): string
    {
        if (strripos($lien,self::YOUTUBE)) {
            return self::YOUTUBE;
        } elseif (strripos($lien,self::VIMEO)) {
            return self::VIMEO;
        } elseif (strripos($lien,self::DAILYMOTION)) {
            return self::DAILYMOTION;
        }

        return 'unknown';
    }

    /**
     * Retoune la clé selon la plateforme
     */
    public function getIdentifer(string $lien, string $platform): string
    {
        $vid = mb_strrchr($lien, '/');
        if (self::YOUTUBE === $platform) {
            if (mb_strrchr($lien, '=')) {
                $vid = mb_strrchr($lien, '=');
            }
            $vid = substr($vid, - strlen($vid) + 1);
        } elseif (self::VIMEO === $platform){
            $vid =  substr($vid, - strlen($vid) + 1);
        }

        return $vid;
    }

}