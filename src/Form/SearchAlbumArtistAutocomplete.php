<?php

namespace App\Form;

use App\Entity\MusicCollection\Album;
use App\Repository\MusicCollection\AlbumRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchAlbumArtistAutocomplete extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Album::class,
            'placeholder' => 'Auteur',
            'choice_label' => 'auteur',
            'choice_value' => 'auteur',
            'searchable_fields' => ['auteur'],
            'query_builder' => function (AlbumRepository $repository) {
                return $repository->createQueryBuilder('album')->orderBy('album.auteur', 'ASC');
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
