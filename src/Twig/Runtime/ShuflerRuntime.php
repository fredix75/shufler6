<?php

namespace App\Twig\Runtime;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ShuflerRuntime implements RuntimeExtensionInterface
{
    const PATTERN_HTTP = '/^(http)?(s)?(:)?(\/\/)?';
    const YOUTUBE = 'youtube.com';
    const YOUTUBE_WWW = 'www.' . self::YOUTUBE;
    const YOUTUBE_API = 'https://img.' . self::YOUTUBE . '/vi/';
    const YOUTUBE_WATCH = 'https://' . self::YOUTUBE_WWW . '/watch?v=';

    /**
     * *********************************
     */
    const DAILYMOTION = 'dailymotion.com';
    const DAILYMOTION_EMBED = '//' . self::DAILYMOTION . '/embed/video';
    const DAILYMOTION_API = 'https://' . self::DAILYMOTION . '/services/oembed?url=';

    /**
     * *********************************
     */
    const VIMEO = 'vimeo.com';
    const VIMEO_PLAYER = 'player.' . self::VIMEO;
    const VIMEO_API = 'https://' . self::VIMEO . '/api/v2/video/';

    private array $videoParameters;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->videoParameters = $parameterBag->get('shufler_video');
    }

    /**
     * Display category
     */
    public function categoryFilter(int $categorie): string
    {
        $categories = [0 => 'ALL'] + $this->videoParameters['categories'];

        return $categories[$categorie] ?? 'autre';
    }

    /**
     * Display genre
     */
    public function genreFilter(int $genre = null): string
    {
        return $this->videoParameters['genres'][$genre] ?? 'Inconnu';
    }

    /**
     * Display Year
     */
    public function yearFilter(int $year = null): ?int
    {
        return ($year > 0) ? $year : null;
    }

    /**
     * Sanitize URL terms for regexp
     */
    private function sanitize(string $terme): string
    {
        return str_replace('/', '\/', $terme);
    }

    /**
     * Retourne la plateforme
     */
    public function getPlatform(string $lien): string
    {
        //@todo parait cheulou ce truc
        if (preg_match(self::PATTERN_HTTP . $this->sanitize(self::YOUTUBE_WWW) . '/', $lien)) {
            return self::YOUTUBE;
        } elseif (strripos($lien,self::VIMEO) || preg_match(self::PATTERN_HTTP . $this->sanitize(self::VIMEO_PLAYER) . '/', $lien)) {
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

    /**
     * Display Frames
     */
    public function convertFrameFilter(string $lien): string
    {
        $frame_prefix = '<img class="embed-responsive-item" src="';
        $width = '100%';
        $frame = $frame_prefix . $this->videoParameters['no_signal'] . '" width=' . $width . ' />';

        $platform = $this->getPlatform($lien);

        $vid = $this->getIdentifer($lien, $platform);

        if ($platform === self::YOUTUBE) {
            $video = self::YOUTUBE_API . $vid .  '/0.jpg';
            $frame = $frame_prefix . $video . '" width=' . $width . ' />';

        } elseif ($platform === self::VIMEO) {

            try {
                $data = null;
                if ($vid != 112297136) { // Exception sur id (pas le choix) --- #la merde
                    $data = file_get_contents(self::VIMEO_API . $vid . '.json');
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
            if ($data != null && $data = json_decode($data)) {
                $frame = $frame_prefix . $data[0]->thumbnail_medium . '" width=' . $width . ' />';
            }
        } elseif (self::DAILYMOTION === $platform) {
            try {
                if (strstr($lien, 'http')) {
                    $vid = $lien;
                } elseif (strstr($lien, '//')) {
                    $vid = 'https:' . $lien;
                } else {
                    $vid = 'https://' . $lien;
                }

                $data = file_get_contents(self::DAILYMOTION_API . $vid);
            } catch (\Exception $e) {
                error_log($e->getMessage());
                $data = null;
            }

            if ($data && $data = json_decode($data)) {
                $frame = $frame_prefix . $data->thumbnail_url . '" width=' . $width . ' />';
            }
        }

        return $frame;
    }

    public function getYoutubeChannelId(string $lien): string
    {
        $pos = mb_strpos($lien, 'list=');
        return mb_substr($lien, $pos + 5);
    }

    /**
     * Display Video Pop-up
     */
    public function popUpFilter(string $lien): string
    {
        $link = $lien;
        $platform = $this->getPlatform($lien);
        $id = $this->getIdentifer($lien, $platform);

        if (self::YOUTUBE === $platform) {
            $link = self::YOUTUBE_WATCH . $id;
        } elseif (self::DAILYMOTION === $platform) {
            $link = self::DAILYMOTION_EMBED . $id;
        }

        return $link;
    }

    public function toIconAlertFilter(string $string): string
    {
        switch ($string) {
            case 'success' :
                return 'bi bi-shield-fill-check';
            case 'warning' :
                return 'bi bi-shield-fill-exclamation';
            case 'danger' :
                return 'bi bi-bug-fill';
            default:
                return $string;
        }

        return $icon;
    }
}
