<?php

namespace App\Form;

use App\Entity\MusicCollection\Artist;
use App\Entity\MusicCollection\VTrack;
use App\Repository\MusicCollection\ArtistRepository;
use App\Repository\MusicCollection\VTrackRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchVArtistAutocomplete extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => VTrack::class,
            'placeholder' => 'Auteur',
            'choice_label' => 'author',
            'choice_value' => 'author',
            'searchable_fields' => ['author'],
            'query_builder' => function (VTrackRepository $repository) {
                return $repository->createQueryBuilder('vt')->where("vt.id in (SELECT MIN(svt.id) FROM App\Entity\MusicCollection\VTrack svt group by svt.author )")->orderBy('vt.author', \SortDirection::Ascending);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
