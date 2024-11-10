<?php

namespace App\Form;

use App\Entity\FilterPiece;
use App\Form\ChoiceLoader\PieceGenresLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterPieceFormType extends AbstractType
{
    public function __construct(private readonly PieceGenresLoader $genresLoader)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la Liste',
                'required' => true,
            ])
            ->add('genres', ChoiceType::class, [
                'label' => 'Genres',
                'required' => false,
                'choice_loader' => $this->genresLoader,
                'multiple' => true,
                'expanded' => true,
                'row_attr' => [
                    'class' => 'form-check form-switch'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterPiece::class,
        ]);
    }
}
