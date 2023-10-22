<?php

namespace App\Form;

use App\Repository\MusicCollection\TrackRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class FilterTracksType extends AbstractType
{
    public function __construct(private readonly TrackRepository $trackRepository){}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => 'Search',
                'required' => false,
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('auteur', TextType::class, [
                'label' => 'Auteur',
                'required' => false,
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('album', TextType::class, [
                'label' => 'Album',
                'required' => false,
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('annee', TextType::class, [
                'label' => 'Année ou Période',
                'required' => false,
                'help' => 'Ex: 1968 ou 1914-1918',
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2',
                ],
                'attr' => [
                    'pattern' => '\d{4}(-\d{4})?',
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'placeholder' => 'Choisissez un genre',
                'required' => false,
                'choices' => $this->getGenres(),
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-2'
                ],
            ])
            ->add('note', ChoiceType::class, [
                'label' => 'Note',
                'placeholder' => '',
                'required' => false,
                'choices' => $this->getNotes(),
                'row_attr' => [
                    'class' => 'col-6 col-md-4 col-lg-1'
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'OK',
                'row_attr' => [
                    'class' => 'col-3 col-md-4 col-lg-1'
                ],
                'attr' => [
                    'style' => 'margin-top: 31px;'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
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
