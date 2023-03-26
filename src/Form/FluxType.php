<?php

namespace App\Form;

use App\Entity\Flux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FluxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('url')
            ->add('image')
            ->add('type')
            ->add('mood')
            ->add('dateInsert')
            ->add('channel')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flux::class,
        ]);
    }
}
