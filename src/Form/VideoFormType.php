<?php

namespace App\Form;

use App\Entity\Mood;
use App\Entity\Video;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class VideoFormType extends AbstractType
{
    private array $videoParameters;

    public function __construct(ParameterBagInterface $parameterBag) {
        $this->videoParameters = $parameterBag->get('shufler_video');
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('auteur', TextType::class)
            ->add('lien', UrlType::class, [
                'attr' => [
                    'data-action' => 'video-edit#completeInfos'
                ]
            ])
            ->add('chapo', TextareaType::class, [
                'required' => false,
            ])
            ->add('texte', TextareaType::class, [
                'required' => false,
            ])
            ->add('annee', IntegerType::class, [
                'required' => false,
                'constraints'   => new Range([
                    'min' => 1900,
                    'max' => (new \DateTime())->format('Y'),
                    'notInRangeMessage' => 'L\'année semble incorrecte']),
                'attr' => [
                    'data-action' => 'video-edit#selectPeriod'
                ]
            ])
            ->add('periode', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Choose a period',
                'choices' => array_combine(
                    $this->videoParameters['periods'],
                    $this->videoParameters['periods']
                ),
                'attr' => [
                    'class' => 'select2'
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Choose a category',
                'choices' => array_flip($this->videoParameters['categories']),
                'attr' => [
                    'class' => 'select2',
                    'data-action' => 'change->video-edit#categorieChange'
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'Choose a genre',
                'choices' => array_flip($this->videoParameters['genres']),
                'attr' => [
                    'class' => 'select2'
                ],
                'row_attr' => [
                    'id' => 'genre',
                ],
            ])
            ->add('moods', EntityType::Class, [
                'class' => Mood::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'required' => true,
                'choices' => array_combine(
                    $this->videoParameters['priorities'],
                    $this->videoParameters['priorities']
                ),
                'attr' => [
                    'class' => 'select2'
                ],
            ])
            ->add('published', CheckboxType::class, [
                'required' => false,
                'row_attr' => [
                    'class' => 'form-check form-switch'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
