<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Nouveau mot de passe'
                    ]
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Répéter le mot de passe'
                    ]
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
            ])
        ;
    }
} 