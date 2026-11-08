<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SearchFrame
{
    private array $datas;

    public function getDatas(): array
    {
        return $this->datas;
    }

    public function setDatas(array $datas): void
    {
        $this->datas = $datas;
    }


}
