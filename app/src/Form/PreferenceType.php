<?php

namespace App\Form;

use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prefereneceBrand', TextType::class, [
                'label' => 'Marque préférée',
                'required' => false,
            ])
            ->add('preferenceModel', TextType::class, [
                'label' => 'Modèle préféré',
                'required' => false,
            ])
            ->add('minYear', DateType::class, [
                'label' => 'Année minimale',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
            ])
            ->add('maxPrice', IntegerType::class, [
                'label' => 'Prix maximum',
                'required' => false,
            ])
            ->add('preferenceFuelType', ChoiceType::class, [
                'label' => 'Type de carburant',
                'choices' => [
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                    'GPL' => 'gpl',
                    'Autre' => 'autre',
                ],
                'required' => false,
                'placeholder' => 'Sélectionnez',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Preference::class,
        ]);
    }
} 