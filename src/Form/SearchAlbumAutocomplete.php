<?php

namespace App\Form;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Artist;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\ArtistRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchAlbumAutocomplete extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Album::class,
            'placeholder' => 'Album',
            'choice_label' => 'name',
            'choice_value' => 'name',
            'searchable_fields' => ['name'],
            'query_builder' => function (AlbumRepository $repository) {
                return $repository->createQueryBuilder('album')->orderBy('album.name', 'ASC');
            }
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
