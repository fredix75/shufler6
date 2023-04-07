<?php

namespace App\Form;

use App\Entity\ChannelFlux;
use App\Entity\Flux;
use App\Repository\ChannelFluxRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class FluxType extends AbstractType
{
    private array $fluxParameters;

    public function __construct(array $fluxParameters) {
        $this->fluxParameters = $fluxParameters;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('url', UrlType::class, [
                'attr' => [
             //       'data-action' => 'change->flux-edit#getImageYoutubePlaylist'
                ],
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => 'Choose a Type',
                'choices' => array_flip($this->fluxParameters['types']),
                'attr' => [
                    'data-action' => 'change->flux-edit#typeChange'
                ],
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => "Le format du fichier n'est pas OK",
                    ])
                ],
                'row_attr' => [
                    'id' => 'file',
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('image', HiddenType::class)
            ->add('mood',ChoiceType::class, [
                'placeholder' => 'Choose a Category',
                'required' => false,
                'choices' => array_flip($this->fluxParameters['rss'] + $this->fluxParameters['radios'] + $this->fluxParameters['links']),
                'row_attr' => [
                    'id' => 'mood',
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('channel', EntityType::class, [
                'placeholder' => 'Choose a Channel',
                'required' => false,
                'class' => ChannelFlux::class,
                'query_builder' => function (ChannelFluxRepository $repo) {
                    return $repo->getChannelFluxAudio();
                },
                'choice_label' => 'name',
                'attr' => [
                      'data-action' => 'modal-channel-form#openModal'
                ],
                'row_attr' => [
                    'id' => 'channel',
                    'class' => 'input-group mb-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flux::class,
        ]);
    }
}
