<?php

namespace App\Form\ChoiceLoader;

use App\Repository\MusicCollection\PieceRepository;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

class PieceGenresLoader implements ChoiceLoaderInterface
{
    private array $genres;

    public function __construct(PieceRepository $pieceRepository) {
        $this->genres = $pieceRepository->getGenres();
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $genres = array_map(function($item) {
            return $item['genre'];
        }, $this->genres);

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