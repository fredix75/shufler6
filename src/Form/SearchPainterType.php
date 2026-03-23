<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchPainterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('painter', SearchPainterAutocomplete::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="bi bi-search"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-outline-secondary pe-3 ps-3',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
