<?php

namespace App\Twig\Runtime;

use App\Helper\VideoHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ShuflerRuntime implements RuntimeExtensionInterface
{
    private array $videoParameters;

    public function __construct(ParameterBagInterface $parameterBag, private readonly VideoHelper $videoHelper)
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
     * Display Frames
     */
    public function convertFrameFilter(string $lien): string
    {
        $frame_prefix = '<img class="embed-responsive-item" src="';
        $width = '100%';
        $frame = $frame_prefix . $this->videoParameters['no_signal'] . '" width=' . $width . ' />';

        $platform = $this->videoHelper->getPlatform($lien);
        $vid = $this->videoHelper->getIdentifer($lien, $platform);

        if ($platform === VideoHelper::YOUTUBE) {
            $video = VideoHelper::YOUTUBE_API . $vid .  '/0.jpg';
            $frame = $frame_prefix . $video . '" width=' . $width . ' />';

        } elseif ($platform === VideoHelper::VIMEO) {

            try {
                $data = null;
                if ($vid != 112297136) { // Exception sur id (pas le choix) --- #la merde
                    $data = file_get_contents(VideoHelper::VIMEO_API . $vid . '.json');
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
            if ($data != null && $data = json_decode($data)) {
                $frame = $frame_prefix . $data[0]->thumbnail_medium . '" width=' . $width . ' />';
            }
        } elseif (VideoHelper::DAILYMOTION === $platform) {
            try {
                $vid = preg_replace('/^(http|https)?(:)?(\/\/)?/', 'https://', $lien);
                $data = file_get_contents(VideoHelper::DAILYMOTION_API . $vid);
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

    public function getYoutubeChannelLinkFilter(string $lien): string
    {
        $pos = mb_strpos($lien, 'list=');
        $pos = $pos !== false ? $pos+5 : 0;
        return VideoHelper::YOUTUBE_WATCH.mb_substr($lien, $pos);
    }

    /**
     * Display Video Pop-up
     */
    public function popUpFilter(string $link): string
    {
        $platform = $this->videoHelper->getPlatform($link);
        $id = $this->videoHelper->getIdentifer($link, $platform);

        if (VideoHelper::YOUTUBE === $platform) {
            $link = VideoHelper::YOUTUBE_WATCH . $id;
        } elseif (VideoHelper::DAILYMOTION === $platform) {
            $link = VideoHelper::DAILYMOTION_EMBED . $id;
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
    }
}
