<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', SearchType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rechercher'
                ],
                'required' => false
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'Label' => 'label',
                    'Date de création' => 'createdAt',
                    'Date de mise à jour' => 'updatedAt',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ])
            ->add('direction', ChoiceType::class, [
                'choices' => [
                    'Croissant' => 'ASC',
                    'Décroissant' => 'DESC',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ])
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
