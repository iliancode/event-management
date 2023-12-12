<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Type;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Delete?',
                'image_uri' => true,
                'download_uri' => true,
                'asset_helper' => true,
            ])
            ->add('description')
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'label',
            ])
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('city')
            ->add('zipcode')
            ->add('address')
            ->add('location')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
