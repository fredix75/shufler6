<?php

namespace App\Form;

use App\Entity\MusicCollection\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtraNotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('extra_graduated_tracks', ChoiceType::class, [
                'choices' => $options['tracks'],
                'choice_value' => fn(Track $track) => $track->getId(),
                'choice_label' => false,
                'multiple' => true,
                'expanded' => true,
                'row_attr'=> [
                    'class' => 'form-check form-switch'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'tracks' => []
        ]);
    }
}
