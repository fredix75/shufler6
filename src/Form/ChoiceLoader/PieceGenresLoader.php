<?php

namespace App\Form\ChoiceLoader;

use App\Repository\MusicCollection\PieceRepository;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

class PieceGenresLoader implements ChoiceLoaderInterface
{

    public function __construct(private readonly PieceRepository $pieceRepository) {}

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $genres = $this->pieceRepository->getGenres();

        $genres = array_map(function($item) {
            return $item['genre'];
        }, $genres);

        return new ArrayChoiceList(array_combine($genres, $genres));
    }

    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        return array_combine($values, $values);
    }

    public function loadValuesForChoices(array $choices, ?callable $value = null): array
    {
        return array_combine($choices, $choices);
    }
}