<?php

namespace App\Form;

use App\Entity\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label' , null, [
                'label' => 'Nom du type',
                'attr' => [
                    'placeholder' => 'Nom du type'
                ]
            ])
            ->add('maxParticipants' , null, [
                'label' => 'Nombre de participants maximum',
                'attr' => [
                    'placeholder' => 'Nombre de participants maximum'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
        ]);
    }
}
