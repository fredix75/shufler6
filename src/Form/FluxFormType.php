<?php

namespace App\Form;

use App\Entity\ChannelFlux;
use App\Entity\Flux;
use App\Repository\ChannelFluxRepository;
use App\Repository\FluxMoodRepository;
use App\Repository\FluxTypeRepository;
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

class FluxFormType extends AbstractType
{
    public function __construct(
        private readonly FluxTypeRepository $fluxTypeRepository,
        private readonly FluxMoodRepository $fluxMoodRepository
    ) {}

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
                'choices' => $this->getTypes(),
                'attr' => [
                    'data-action' => 'change->flux-edit#typeChange',
                    'class' => 'select2',
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
                'choices' => $this->getMoods(),
                'attr' => [
                    'class' => 'select2',
                ],
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
                    'data-action' => 'modal-channel-form#openModal',
                    'class' => 'select2',
                ],
                'row_attr' => [
                    'id' => 'channel',
                    'class' => 'input-group mb-3'
                ]
            ])
        ;
    }

    private function getTypes(): array
    {
        $types = $this->fluxTypeRepository->findAll();
        $types = array_map(function($item) {
            return [$item->getId() => $item->getName()];
        }, $types);
        array_walk_recursive($types, function($k, $a) use (&$return) {
            $return[$k] = $a;
        });
        return $return;
    }

    private function getMoods(): array
    {
        $moods = $this->fluxMoodRepository->findAll();
        $moods = array_map(function($item) {
            $key = $item->getCode();
            $item = $item->getName();
            return [$key => $item];
        }, $moods);
        array_walk_recursive($moods, function($k, $a) use (&$return) {
            $return[$k] = $a;
        });

        return $return;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flux::class,
        ]);
    }
}
