<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FrixturExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrixturExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('search_form', [
                FrixturExtensionRuntime::class, 'getSearchForm']
            ),
        ];
    }
}
