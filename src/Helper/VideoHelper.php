<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VideoHelper
{
    const YOUTUBE = 'youtube';
    const YOUTUBE_ALT = 'youtu.be';
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

    public function __construct(private readonly ParameterBagInterface $parameterBag, private readonly SerializerInterface $serializer) {}

    public function getPlatform(string $lien): string
    {
        if (strripos($lien,self::YOUTUBE) || strripos($lien,self::YOUTUBE_ALT)) {
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

    public function selectPeriod(int $annee): string
    {
        $periodes = $this->parameterBag->get('shufler_video')['periods'];
        $periode = $periodes[count($periodes)-1];
        if ($annee >= 1939) {
            array_map(function($value) use ($annee, &$periode) {
                if ($annee >= explode('-', $value)[0] && $annee <= explode('-', $value)[1]) {
                    $periode = $value;
                }
            }, $periodes);
        }

        return $periode;
    }

    public function buildPlaylist(array $videos): array
    {
        $videoParameters = $this->parameterBag->get('shufler_video');

        $trackIntro = [
            'titre' => ' * * * * * L O A D I N G * * * * * ',
            'auteur' => '',
            'annee' => null,
            'youtubeKey' => $videoParameters['intro_couch']
        ];

        $list = [$trackIntro];
        $playlist = [$trackIntro['youtubeKey']];

        $i = 0;
        foreach ($videos as $video) {
            $video = $this->serializer->normalize($video);

            if (!\in_array($this->getIdentifer($video['lien'], 'youtube'), $playlist)) {
                $video['youtubeKey'] = $this->getIdentifer($video['lien'], 'youtube');
                $list[] = $video;
                $playlist[] = $video['youtubeKey'];
                $i++;
            }
            if ($i >= $videoParameters['max_list_couch']) {
                break;
            }
        }

        return [$list, $playlist];
    }
}
