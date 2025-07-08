<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Formulaire d'inscription des nouveaux utilisateurs
 * 
 * Ce formulaire gère :
 * - La collecte des informations personnelles (nom, prénom, email)
 * - La saisie et validation du mot de passe
 * - Les informations d'adresse complètes
 * - Le consentement RGPD obligatoire
 * - La validation des données avec contraintes Symfony
 * - L'interface utilisateur avec placeholders et labels
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Construction du formulaire d'inscription
     * 
     * Cette méthode définit tous les champs du formulaire avec :
     * - Les types de champs appropriés (TextType, EmailType, etc.)
     * - Les labels en français
     * - Les placeholders pour l'interface utilisateur
     * - Les contraintes de validation
     * - Les attributs HTML spécifiques
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ prénom - Texte simple
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Votre prénom'
                ]
            ])
            
            // Champ nom - Texte simple
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom'
                ]
            ])
            
            // Champ email - Validation automatique du format email
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Votre email'
                ]
            ])
            
            // Champ mot de passe - Non mappé à l'entité, validation personnalisée
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // Ne pas mapper directement à l'entité
                'attr' => [
                    'autocomplete' => 'new-password', // Attribut pour les navigateurs
                    'placeholder' => 'Votre mot de passe'
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    // Contrainte : longueur du mot de passe (6-4096 caractères)
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096, // Limite de sécurité Symfony
                    ]),
                ],
            ])
            
            // Champ adresse - Texte simple
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Votre adresse'
                ]
            ])
            
            // Champ ville - Texte simple
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Votre ville'
                ]
            ])
            
            // Champ code postal - Texte simple (validation côté entité)
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Votre code postal'
                ]
            ])
            
            // Champ pays - Liste déroulante des pays avec France préférée
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'], // France en premier dans la liste
                'attr' => [
                    'placeholder' => 'Votre pays'
                ]
            ])
            
            // Champ téléphone - Type téléphone pour validation mobile
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'placeholder' => 'Votre numéro de téléphone'
                ]
            ])
            
            // Champ consentement RGPD - Obligatoire pour la conformité
            ->add('rgpdConsent', CheckboxType::class, [
                'label' => 'J\'accepte la politique de confidentialité et le traitement de mes données personnelles conformément au RGPD',
                'mapped' => false, // Ne pas mapper à l'entité
                'constraints' => [
                    // Contrainte : doit être coché pour valider
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions de traitement des données personnelles.',
                    ]),
                ],
                'label_attr' => [
                    'class' => 'checkbox-label' // Classe CSS pour le style
                ]
            ])
        ;
    }

    /**
     * Configuration des options du formulaire
     * 
     * Cette méthode définit :
     * - La classe d'entité associée au formulaire
     * - Les options par défaut pour la validation
     * 
     * @param OptionsResolver $resolver Résolveur d'options Symfony
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class, // Entité associée au formulaire
        ]);
    }
} 