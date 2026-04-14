<?php

namespace App\Form;

use App\DataTransformer\FilmGenresTransformer;
use App\Entity\Film;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('altName')
            ->add('offset', IntegerType::class, [
                'required' => false,
                'mapped' => false,
                'data' => 1,
            ])
            ->add('year')
            ->add('picture')
            ->add('overview')
            ->add('originalLanguage')
            ->add('originalTitle')
            ->add('tmdbId')
            ->add('posterPath')
            ->add('backdropPath')
            ->add('popularity')
            ->add('genres')
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);

        $builder->get('genres')->addModelTransformer(new FilmGenresTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
        ]);
    }
}
