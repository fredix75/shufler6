<?php

namespace App\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterVideosFormType extends AbstractType
{
    public function __construct(private readonly ParameterBagInterface $parameterBag){}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search',
                ],
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('periode', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez une période',
                'required' => false,
                'choices' => array_combine(
                    $this->parameterBag->get('shufler_video')['periods'],
                    $this->parameterBag->get('shufler_video')['periods']
                ),
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez une catégorie',
                'required' => false,
                'choices' => array_flip($this->parameterBag->get('shufler_video')['categories']),
                'attr' => [
                    'data-action' => 'change->video-couch#displayGenres'
                ],
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez un genre',
                'required' => false,
                'choices' => array_flip($this->parameterBag->get('shufler_video')['genres']),
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'OK',
                'row_attr' => [
                    'class' => 'col-3 col-md-4 col-lg-1'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
