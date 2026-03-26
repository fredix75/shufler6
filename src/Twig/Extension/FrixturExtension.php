<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FrixturExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FrixturExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('periodeDisplay', [FrixturExtensionRuntime::class, 'periodeFilter']),
        ];
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('search_form', [
                FrixturExtensionRuntime::class, 'getSearchForm']
            ),
        ];
    }
}
