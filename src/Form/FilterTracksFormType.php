<?php

namespace App\Form;

use App\Form\ChoiceLoader\PieceGenresLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterTracksFormType extends AbstractType
{
    public function __construct(private readonly PieceGenresLoader $genresLoader)
    {
    }

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
            ->add('auteur', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Auteur',
                ],
            ])
            ->add('album', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Album',
                ],
            ])
            ->add('annee', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'pattern' => '\d{4}(-\d{4})?',
                    'placeholder' => 'Ex: 1968 ou 1914-1918',
                ],
            ])
            ->add('genres', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez un genre',
                'required' => false,
                'choice_loader' => $this->genresLoader,
                'multiple' => true,
                'attr'  => [
                    'class' => 'select2',
                ],
            ]);

            if ($options['mode'] !== 'album') {
                $builder->add('note', ChoiceType::class, [
                    'label' => false,
                    'placeholder' => 'Rating',
                    'required' => false,
                    'choice_loader' => new CallbackChoiceLoader(static function (): array {
                        for($i = 5; $i > 0; $i--){
                            $notes[$i] = $i;
                        }
                        return $notes;
                    }),
                ]);
            } else {
                $builder->add('page', HiddenType::class, [
                        'data' => 0,
                ])->add('random', CheckboxType::class, [
                        'required' => false,
                    ]);
            }

            $builder->add('submit', SubmitType::class, [
                'label' => 'OK',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'mode' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
