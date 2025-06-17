<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => true,
            ])
            ->add('postalCode', NumberType::class, [
                'label' => 'Code postal',
                'required' => true,
                'attr' => [
                    'min' => 1000,
                    'max' => 99999,
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => true,
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
                'attr' => [
                    'pattern' => '[0-9]{10}',
                    'placeholder' => '0123456789',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
} 