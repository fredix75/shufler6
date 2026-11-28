<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadRadialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => 'Fichier CSV',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File(
                    maxSize: '2M',
                    mimeTypes: [
                        'text/csv',
                        'text/plain',
                        'application/csv',
                    ],
                    mimeTypesMessage: 'Format de fichier non valide.',
                )
            ],
        ]);
    }
}
