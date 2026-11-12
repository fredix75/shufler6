<?php

namespace App\Form;

use App\Entity\FilterPiece;
use App\Entity\Genrefilm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterFilmsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search',
                ],
            ])
            ->add('genres', EntityType::class, [
                'class' => Genrefilm::class,
                'required' => false,
                'multiple' => true,
                'choice_label' => 'name',
                'attr' => [
                    'placeholder' => 'Genre',
                    'class' => 'select2',
                ]
            ])
            ->add('director', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Director',
                ]
            ])
            ->add('annee', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'pattern' => '\d{4}(-\d{4})?',
                    'placeholder' => 'Ex: 1968 ou 1914-1918',
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}

