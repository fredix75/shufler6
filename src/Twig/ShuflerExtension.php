<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ShuflerExtension extends AbstractExtension
{
    const PATTERN_HTTP = '/^(http)?(s)?(:)?(\/\/)?';
    const YOUTUBE = 'youtube.com';
    const YOUTUBE_WWW = 'www.' . self::YOUTUBE;
    const YOUTUBE_API = 'https://img.' . self::YOUTUBE . '/vi/';
    const YOUTUBE_EMBED = 'https://' . self::YOUTUBE_WWW . '/embed/';
    const YOUTUBE_WATCH = 'https://' . self::YOUTUBE_WWW . '/watch?v=';
    const YOUTUBE_SHARE = 'https://youtu.be/';

    /**
     * *********************************
     */
    const DAILYMOTION = 'dailymotion.com';
    const DAILYMOTION_WWW = 'www.' . self::DAILYMOTION;
    const DAILYMOTION_VIDEO = 'http://' . self::DAILYMOTION_WWW . '/video/';
    const DAILYMOTION_EMBED = '//' . self::DAILYMOTION_WWW . '/embed/video/';
    const DAILYMOTION_API = 'http://' . self::DAILYMOTION_WWW . '/services/oembed?url=';

    /**
     * *********************************
     */
    const VIMEO = 'vimeo.com';
    const VIMEO_HTTPS = 'https://' . self::VIMEO . '/';
    const VIMEO_PLAYER = 'player.' . self::VIMEO;
    const VIMEO_PLAYER_HTTPS = 'https://' . self::VIMEO_PLAYER . '/';
    const VIMEO_VIDEO = '//player.' . self::VIMEO . '/video/';
    const VIMEO_API = 'https://' . self::VIMEO . '/api/v2/video/';
    const VIMEO_STAFFPICKS = 'https://' . self::VIMEO . '/channels/staffpicks/';
    const VIDEO_UNAVAILABLE = 'http://s3.amazonaws.com/colorcombos-images/users/1/color-schemes/color-scheme-2-main.png?v=20111009081033';
    private array $videoParameters;

    public function __construct(array $videoParameters) {
        $this->videoParameters = $videoParameters;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('categorieDisplay', [
                $this,
                'categoryFilter'
            ]),
            new TwigFilter('genreDisplay', [
                $this,
                'genreFilter'
            ]),
            new TwigFilter('yearDisplay', [
                $this,
                'yearFilter'
            ]),
            new TwigFilter('convertFrame', [
                $this,
                'convertFrameFilter'
            ]),
            new TwigFilter('popUp', [
                $this,
                'popUpFilter'
            ])
        ];
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
    public function genreFilter(int $genre = -1): string
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
        if (preg_match(self::PATTERN_HTTP . $this->sanitize(self::YOUTUBE_WWW) . '/', $lien)) {
            return self::YOUTUBE;
        } elseif (preg_match(self::PATTERN_HTTP . $this->sanitize(self::VIMEO_PLAYER) . '/', $lien)) {
            return self::VIMEO;
        } elseif (preg_match(self::PATTERN_HTTP . $this->sanitize(self::DAILYMOTION_WWW) . '/', $lien)) {
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
            $vid = substr($vid, - strlen($vid) + 1);
        } elseif (self::DAILYMOTION === $platform) {
            // ?
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
        $frame = $frame_prefix . self::VIDEO_UNAVAILABLE . '" width=' . $width . ' />';

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
            $link = self::DAILYMOTION_VIDEO . $id;
        }

        return $link;
    }

    public function getName(): string
    {
        return 'shufler_extension';
    }
}