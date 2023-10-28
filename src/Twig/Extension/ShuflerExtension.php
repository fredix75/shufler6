<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ShuflerRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ShuflerExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('categorieDisplay', [ShuflerRuntime::class, 'categoryFilter']),
            new TwigFilter('genreDisplay', [ShuflerRuntime::class, 'genreFilter']),
            new TwigFilter('yearDisplay', [ShuflerRuntime::class, 'yearFilter']),
            new TwigFilter('youtubeChannelLink', [ShuflerRuntime::class, 'getYoutubeChannelLinkFilter']),
            new TwigFilter('popUp', [ShuflerRuntime::class, 'popUpFilter']),
            new TwigFilter('popUpYoutube', [ShuflerRuntime::class, 'popUpYoutubeFilter']),
            new TwigFilter('toIconAlert', [ShuflerRuntime::class, 'toIconAlertFilter'])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('categorieDisplay', [ShuflerRuntime::class, 'categoryFilter']),
            new TwigFunction('genreDisplay', [ShuflerRuntime::class, 'genreFilter']),
            new TwigFunction('yearDisplay', [ShuflerRuntime::class, 'yearFilter']),
            new TwigFunction('convertFrame', [ShuflerRuntime::class, 'convertFrameFilter'], ['is_safe' => ['html']]),
            new TwigFunction('youtubeChannelLink', [ShuflerRuntime::class, 'getYoutubeChannelLinkFilter']),
            new TwigFunction('popUp', [ShuflerRuntime::class, 'popUpFilter']),
            new TwigFunction('toIconAlert', [ShuflerRuntime::class, 'toIconAlertFilter'])
        ];
    }
}
