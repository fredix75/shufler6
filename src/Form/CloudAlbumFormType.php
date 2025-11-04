<?php

namespace App\Form;

use App\Entity\MusicCollection\CloudAlbum;
use App\Form\ChoiceLoader\PieceGenresLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CloudAlbumFormType extends AbstractType
{
    public function __construct(private readonly PieceGenresLoader $genresLoader){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('youtubeKey')
            ->add('name')
            ->add('auteur')
            ->add('annee', IntegerType::class, [
                'required' => false,
            ])
            ->add('genre', ChoiceType::class, [
                'choice_loader' => $this->genresLoader,
                'placeholder' => ' -- Sélectionnez un genre -- ',
                'attr' => [
                    'class' => 'select2'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CloudAlbum::class,
        ]);
    }
}
