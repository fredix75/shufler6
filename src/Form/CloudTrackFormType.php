<?php

namespace App\Form;

use App\Entity\MusicCollection\CloudTrack;
use App\Form\ChoiceLoader\PieceGenresLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CloudTrackFormType extends AbstractType
{
    public function __construct(private readonly PieceGenresLoader $genresLoader){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('youtubeKey', TextType::class, [
                'label' => 'Youtube Link',
            ])
            ->add('auteur', TextType::class, [
                'required' => false,
            ])
            ->add('titre', TextType::class, [
                'required' => false,
            ])
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
            ->add('pays')
            ->add('extraNote', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Rating',
                'required' => false,
                'choice_loader' => new CallbackChoiceLoader(static function (): array {
                    for($i = 5; $i > 0; $i--){
                        $notes[$i] = $i;
                    }
                    return $notes;
                }),
                'attr' => [
                    'class' => 'select2'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CloudTrack::class,
        ]);
    }
}
