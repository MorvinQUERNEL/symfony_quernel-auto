<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;

class CombinedFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Section Inscription
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Votre prénom'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre prénom'),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Votre nom'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre nom'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Votre email'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre email'),
                    new Email(message: 'Veuillez entrer un email valide'),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Votre mot de passe'
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer un mot de passe'),
                    new Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        max: 4096,
                    ),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Votre adresse'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre adresse'),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Votre ville'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre ville'),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['placeholder' => 'Votre code postal'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre code postal'),
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'],
                'attr' => ['placeholder' => 'Votre pays'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner votre pays'),
                ],
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['placeholder' => 'Votre numéro de téléphone'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre numéro de téléphone'),
                ],
            ])
            ->add('rgpdConsent', CheckboxType::class, [
                'label' => 'J\'accepte la politique de confidentialité et le traitement de mes données personnelles conformément au RGPD',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions de traitement des données personnelles.',
                    ]),
                ],
                'label_attr' => [
                    'class' => 'checkbox-label'
                ]
            ])
            
            // Section Préférences
            ->add('prefereneceBrand', TextType::class, [
                'label' => 'Marque préférée',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Ex: Mercedes, BMW, Audi...'],
            ])
            ->add('preferenceModel', TextType::class, [
                'label' => 'Modèle préféré',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Ex: Classe A, Série 3, A4...'],
            ])
            ->add('minYear', DateType::class, [
                'label' => 'Année minimale',
                'widget' => 'single_text',
                'required' => false,
                'mapped' => false,
                'html5' => true,
            ])
            ->add('maxPrice', IntegerType::class, [
                'label' => 'Prix maximum',
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'En euros'],
            ])
            ->add('preferenceFuelType', ChoiceType::class, [
                'label' => 'Type de carburant',
                'choices' => [
                    'Indifférent' => '',
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                    'GPL' => 'gpl',
                    'Autre' => 'autre',
                ],
                'required' => false,
                'mapped' => false,
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