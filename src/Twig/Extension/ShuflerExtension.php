<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ShuflerExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ShuflerExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('categorieDisplay', [ShuflerExtensionRuntime::class, 'categoryFilter']),
            new TwigFilter('genreDisplay', [ShuflerExtensionRuntime::class, 'genreFilter']),
            new TwigFilter('yearDisplay', [ShuflerExtensionRuntime::class, 'yearFilter']),
            new TwigFilter('youtubeChannelLink', [ShuflerExtensionRuntime::class, 'getYoutubeChannelLinkFilter']),
            new TwigFilter('popUp', [ShuflerExtensionRuntime::class, 'popUpFilter']),
            new TwigFilter('popUpYoutube', [ShuflerExtensionRuntime::class, 'popUpYoutubeFilter']),
            new TwigFilter('toIconAlert', [ShuflerExtensionRuntime::class, 'toIconAlertFilter']),
            new TwigFilter('toIconYoutube', [ShuflerExtensionRuntime::class, 'toIconYoutubeFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('categorieDisplay', [ShuflerExtensionRuntime::class, 'categoryFilter']),
            new TwigFunction('genreDisplay', [ShuflerExtensionRuntime::class, 'genreFilter']),
            new TwigFunction('yearDisplay', [ShuflerExtensionRuntime::class, 'yearFilter']),
            new TwigFunction('convertFrame', [ShuflerExtensionRuntime::class, 'convertFrameFilter'], ['is_safe' => ['html']]),
            new TwigFunction('youtubeChannelLink', [ShuflerExtensionRuntime::class, 'getYoutubeChannelLinkFilter']),
            new TwigFunction('popUp', [ShuflerExtensionRuntime::class, 'popUpFilter']),
            new TwigFunction('toIconAlert', [ShuflerExtensionRuntime::class, 'toIconAlertFilter']),
            new TwigFunction('displayStars', [ShuflerExtensionRuntime::class, 'displayStarsFunction'], ['is_safe' => ['html']])
        ];
    }
}
