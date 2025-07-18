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

/**
 * Formulaire combiné d'inscription et de préférences véhicules
 * 
 * Ce formulaire unique gère :
 * - L'inscription complète d'un nouvel utilisateur
 * - La saisie des préférences de véhicules en même temps
 * - La validation des données personnelles et des préférences
 * - L'interface utilisateur unifiée pour une meilleure UX
 * - Les contraintes de validation appropriées pour chaque section
 * - La gestion des champs mappés et non mappés
 */
class CombinedFormType extends AbstractType
{
    /**
     * Construction du formulaire combiné
     * 
     * Cette méthode définit deux sections principales :
     * 1. Section Inscription : Données personnelles obligatoires
     * 2. Section Préférences : Critères de recherche de véhicules optionnels
     * 
     * Les champs de préférences sont non mappés (mapped => false) car ils
     * seront traités séparément pour créer l'entité Preference.
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ===== SECTION INSCRIPTION =====
        // Champ prénom - Texte obligatoire
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Votre prénom'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre prénom'),
                ],
            ])
            
            // Champ nom - Texte obligatoire
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Votre nom'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre nom'),
                ],
            ])
            
            // Champ email - Validation automatique du format email
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Votre email'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre email'),
                    new Email(message: 'Veuillez entrer un email valide'),
                ],
            ])
            
            // Champ mot de passe - Non mappé, validation personnalisée
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // Ne pas mapper à l'entité Users
                'attr' => [
                    'autocomplete' => 'new-password', // Attribut pour les navigateurs
                    'placeholder' => 'Votre mot de passe'
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer un mot de passe'),
                    new Length(
                        min: 12,
                        minMessage: 'Le mot de passe doit contenir au moins 12 caractères.',
                        max: 4096,
                    ),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule.'
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[a-z]/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre minuscule.'
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[^a-zA-Z0-9]/',
                        'message' => 'Le mot de passe doit contenir au moins un caractère spécial (ex : !@#$%^&*).'
                    ]),
                ],
            ])
            
            // Champ adresse - Texte obligatoire
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Votre adresse'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre adresse'),
                ],
            ])
            
            // Champ ville - Texte obligatoire
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Votre ville'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre ville'),
                ],
            ])
            
            // Champ code postal - Texte obligatoire
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['placeholder' => 'Votre code postal'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre code postal'),
                ],
            ])
            
            // Champ pays - Liste déroulante avec France préférée
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'], // France en premier
                'attr' => ['placeholder' => 'Votre pays'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner votre pays'),
                ],
            ])
            
            // Champ téléphone - Type téléphone pour validation mobile
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['placeholder' => 'Votre numéro de téléphone'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre numéro de téléphone'),
                ],
            ])
            
            // Champ consentement RGPD - Obligatoire pour la conformité
            ->add('rgpdConsent', CheckboxType::class, [
                'label' => 'J\'accepte la politique de confidentialité et le traitement de mes données personnelles conformément au RGPD',
                'mapped' => false, // Ne pas mapper à l'entité
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions de traitement des données personnelles.',
                    ]),
                ],
                'label_attr' => [
                    'class' => 'checkbox-label' // Classe CSS pour le style
                ]
            ])
            
            // ===== SECTION PRÉFÉRENCES VÉHICULES =====
            // Champ marque préférée - Texte optionnel, non mappé
            ->add('prefereneceBrand', TextType::class, [
                'label' => 'Marque préférée',
                'required' => false, // Champ optionnel
                'mapped' => false, // Ne pas mapper à l'entité Users
                'attr' => ['placeholder' => 'Ex: Mercedes, BMW, Audi...'],
            ])
            
            // Champ modèle préféré - Texte optionnel, non mappé
            ->add('preferenceModel', TextType::class, [
                'label' => 'Modèle préféré',
                'required' => false, // Champ optionnel
                'mapped' => false, // Ne pas mapper à l'entité Users
                'attr' => ['placeholder' => 'Ex: Classe A, Série 3, A4...'],
            ])
            
            // Champ année minimale - Date optionnelle, non mappée
            ->add('minYear', DateType::class, [
                'label' => 'Année minimale',
                'widget' => 'single_text', // Utilise l'input date HTML5
                'required' => false, // Champ optionnel
                'mapped' => false, // Ne pas mapper à l'entité Users
                'html5' => true, // Utilise les contrôles HTML5 natifs
            ])
            
            // Champ prix maximum - Nombre entier optionnel, non mappé
            ->add('maxPrice', IntegerType::class, [
                'label' => 'Prix maximum',
                'required' => false, // Champ optionnel
                'mapped' => false, // Ne pas mapper à l'entité Users
                'attr' => ['placeholder' => 'En euros'],
            ])
            
            // Champ type de carburant préféré - Liste déroulante optionnelle
            ->add('preferenceFuelType', ChoiceType::class, [
                'label' => 'Type de carburant',
                'choices' => [
                    'Indifférent' => '', // Valeur vide pour "pas de préférence"
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                    'GPL' => 'gpl',
                    'Autre' => 'autre',
                ],
                'required' => false, // Champ optionnel
                'mapped' => false, // Ne pas mapper à l'entité Users
            ])
        ;
    }

    /**
     * Configuration des options du formulaire
     * 
     * Cette méthode définit :
     * - La classe d'entité principale associée (Users)
     * - Les options par défaut pour la validation
     * 
     * Note : Les champs de préférences sont non mappés et seront traités
     * séparément dans le contrôleur pour créer l'entité Preference.
     * 
     * @param OptionsResolver $resolver Résolveur d'options Symfony
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class, // Entité principale associée
        ]);
    }
} 