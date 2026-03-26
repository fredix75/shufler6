<?php

namespace App\Twig\Runtime;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Form\SearchPainterType;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

class FrixturExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private FormFactoryInterface $formFactory, private UrlGeneratorInterface $urlGenerator){}

    private function toRoman(int $nb): string|int
    {
        switch ($nb) {
            case 1:
                return 'I';
            case 2:
                return 'II';
            case 3:
                return 'III';
            case 4:
                return 'IV';
            case 5:
                return 'V';
            case 6:
                return 'VI';
            case 7:
                return 'VII';
            case 8:
                return 'VIII';
            case 9:
                return 'IX';
            case 10:
                return 'X';
            case 11:
                return 'XI';
            case 12:
                return 'XII';
            case 13:
                return 'XIII';
            case 14:
                return 'XIV';
            case 15:
                return 'XV';
            case 16:
                return 'XVI';
            case 17:
                return 'XVII';
            case 18:
                return 'XVIII';
            case 19:
                return 'XIX';
            case 20:
                return 'XX';
            case 21:
                return 'XXI';
            default:
                return $nb;
        }
    }

    public function periodeFilter(int $periodeNumber): string
    {
        $str = '';

        if ($periodeNumber >= 14 && $periodeNumber <= 20) {
            $str = sprintf('Les artistes du %se siècle', $this->toRoman($periodeNumber));
            if ($periodeNumber === 14) {
                $str .= ' et avant';
            }
        }

        return $str;
    }

    public function getSearchForm()
    {
        $form = $this->formFactory->create(SearchPainterType::class, null, [
            'action' => $this->urlGenerator->generate('picture_search'),
        ]);

        return $form->createView();
    }
}
