<?php

namespace App\Twig\Runtime;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Form\SearchPainterType;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

class FrixturExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private FormFactoryInterface $formFactory, private UrlGeneratorInterface $urlGenerator){}

    public function getSearchForm()
    {
        $form = $this->formFactory->create(SearchPainterType::class, null, [
            'action' => $this->urlGenerator->generate('picture_search'),
        ]);

        return $form->createView();
    }
}
