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
            ->add('title' , null, [
                'label' => 'Titre'
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Delete?',
                'image_uri' => true,
                'download_uri' => true,
                'asset_helper' => true,
            ])
            ->add('description' , null, [
                'label' => 'Description'
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'label',
            ])
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('city' , null, [
                'label' => 'Ville'
            ])
            ->add('zipcode' , null, [
                'label' => 'Code postal'
            ])
            ->add('address' , null, [
                'label' => 'Adresse'
            ])
            ->add('location' , null, [
                'label' => 'Lieu'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
