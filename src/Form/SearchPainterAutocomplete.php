<?php

namespace App\Form;

use App\Entity\Frixtur\Painter;
use App\Repository\Frixtur\PainterRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchPainterAutocomplete extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Painter::class,
            'placeholder' => 'Chercher un peintre',
            'choice_label' => fn(Painter $painter) => sprintf('%s %s', $painter->getFirstName(), $painter->getName()),
            'searchable_fields' => ['name', 'firstName'],
            'query_builder' => function (PainterRepository $repository) {
                return $repository->createQueryBuilder('painter')->orderBy('painter.name', 'ASC');
            }
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
