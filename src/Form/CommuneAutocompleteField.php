<?php

namespace App\Form;

use App\Entity\Commune;
use App\Repository\CommuneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class CommuneAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Commune::class,
            'placeholder' => 'Saisissez une Commune, un code postal',
            'choice_label' => fn(Commune $commune) => sprintf('%s (%s)', $commune->getNom(), $commune->getCp()),
            'choice_value' => 'id',
            //'searchable_fields' => ['nom', 'cp'],
            'query_builder' => function (CommuneRepository $repository) {
                return $repository->createQueryBuilder('c')->orderBy('c.nom', 'ASC');
            },
            'filter_query' => function(QueryBuilder $qb, string $query) {
                if (!$query) {
                    return;
                }

                $qb->andWhere('c.nom LIKE :filter OR c.cp LIKE :filter')
                    ->setParameter('filter', '%'.$query.'%')
                    ->orderBy('c.population', 'DESC')
                    ->addOrderBy('c.nom', 'ASC')
                ;
            },

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
