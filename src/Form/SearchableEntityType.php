<?php

namespace App\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchableEntityType extends AbstractType
{

    public function __construct(private EntityManagerInterface $em)
    {

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        //dd($resolver);
        $resolver->setRequired('class');
        $resolver->setDefaults([
            'compound' => false,
            'multiple' => true,
            'search' => '/search'
        ]);
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (Collection $value): array
            {
                return $value->map(fn($d) => (string)$d->getId())->toArray();
            },
            function(array $ids) use ($options): Collection
            {
                if (empty($ids)) {
                    return new ArrayCollection([]);
                }
                return new ArrayCollection(
                    $this->em->getRepository($options['class'])->findBy(['id' => $ids])
                );
            }
        ));
    }


    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['expanded'] = false;
        $view->vars['placeholder'] = false;
        $view->vars['placeholder_in_choices'] = false;
        $view->vars['multiple'] = true;
        $view->vars['choices'] = $this->choices($form->getData());
        $view->vars['choice_translation_domain'] = null;
        $view->vars['preferred_choices'] = [];
        $view->vars['attr']['data-remote'] = $options['search'];
        //$view->vars['full_name'] = '[]';
    }

    public function getBlockPrefix(): string
    {
        return 'choice';
    }

    private function choices(Collection $value): array
    {
        return $value->map(fn ($d) => new ChoiceView($d, (string)$d->getId(), (string)$d))
            ->toArray();
    }
}
