<?php

namespace App\Form;

use App\Repository\MusicCollection\TrackRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class FilterTracksFormType extends AbstractType
{
    public function __construct(private readonly TrackRepository $trackRepository){}
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
            ->add('auteur', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Auteur',
                ],
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('album', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Album',
                ],
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('annee', TextType::class, [
                'label' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2',
                ],
                'attr' => [
                    'pattern' => '\d{4}(-\d{4})?',
                    'placeholder' => 'Ex: 1968 ou 1914-1918',
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez un genre',
                'required' => false,
                'choices' => $this->getGenres(),
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ]);

            if ($options['mode'] !== 'album') {
                $builder->add('note', ChoiceType::class, [
                    'label' => false,
                    'placeholder' => 'Rating',
                    'required' => false,
                    'choices' => $this->getNotes(),
                    'row_attr' => [
                        'class' => 'col-6 col-md-4 col-lg-1'
                    ],
                ]);
            } else {
                $builder->add('page', HiddenType::class, [
                        'data' => 0,
                ])->add('random', CheckboxType::class, [
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-check form-switch col-6 col-md-2 col-lg-1'
                        ]
                    ]);
            }

            $builder->add('submit', SubmitType::class, [
                'label' => 'OK',
                'row_attr' => [
                    'class' => 'col-3 col-md-2 col-lg-1'
                ],
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

    private function getGenres(): array
    {
        $genres = $this->trackRepository->getGenres();

        $genres = array_map(function($item) {
            return $item['genre'];
        }, $genres);

        return array_combine($genres, $genres);
    }

    private function getNotes(): array
    {
        for($i=5; $i>0; $i--){
            $notes[$i] = $i;
        }
        return $notes;
    }
}
