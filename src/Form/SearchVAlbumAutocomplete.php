<?php

namespace App\Form;

use App\Entity\MusicCollection\VTrack;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\VTrackRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchVAlbumAutocomplete extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => VTrack::class,
            'placeholder' => 'Album',
            'choice_label' => 'albumName',
            'searchable_fields' => ['albumName'],
            'query_builder' => function (VTrackRepository $repository) {
                return $repository->createQueryBuilder('vt')->where("vt.id in (SELECT MIN(svt.id) FROM App\Entity\MusicCollection\VTrack svt group by svt.albumName )")->orderBy('vt.author', \SortDirection::Ascending);
            }
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
