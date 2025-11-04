<?php

namespace App\Form;

use App\Entity\ChannelFlux;
use App\Entity\Flux;
use App\Entity\FluxMood;
use App\Entity\FluxType;
use App\Repository\ChannelFluxRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FluxFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('url', UrlType::class, [
                'default_protocol' => 'https',
                'attr' => [
             //       'data-action' => 'change->flux-edit#getImageYoutubePlaylist'
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => FluxType::class,
                'placeholder' => 'Choose a Type',
                'choice_label' => 'name',
                'attr' => [
                    'data-action' => 'change->flux-edit#typeChange',
                    'class' => 'select2',
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'row_attr' => [
                    'id' => 'file',
                ],
                'help' => 'Formats: .jpeg, .jpg or .png. Max: 1Mo',
            ])
            ->add('mood',EntityType::class, [
                'class' => FluxMood::class,
                'placeholder' => 'Choose a Category',
                'required' => false,
                'choices' => $options['moods'],
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'select2',
                ],
                'row_attr' => [
                    'id' => 'mood',
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
                    'data-action' => 'modal-channel-form#openModal',
                    'class' => 'select2',
                ],
                'row_attr' => [
                    'id' => 'channel',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flux::class,
            'moods' => [],
        ]);
    }
}
