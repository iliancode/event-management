<?php

namespace App\Form;

use App\Entity\Type;
use App\Repository\EventRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFilterFormType extends AbstractType
{
    public function __construct
    (
        private readonly EventRepository $eventRepository
    )
    {
    }

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
            ->add('cities', ChoiceType::class, [
                'choices' => $this->eventRepository->getUniqueCities(),
                'choice_label' => fn($choice) => $choice,
                'group_by' => fn($choice) => mb_substr($choice, 0, 1),
                'multiple' => true,
                'autocomplete' => true
            ])
            ->add('types', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'label',
                'multiple' => true,
                'required' => false,
                'autocomplete' => true
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'A venir' => 'coming',
                    'Passé' => 'past',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Après le',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'Avant le',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('available', ChoiceType::class, [
                'choices' => [
                    'Places disponibles' => true,
                    'Au complet' => false,
                ],
                'expanded' => true,
                'multiple' => true,
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
