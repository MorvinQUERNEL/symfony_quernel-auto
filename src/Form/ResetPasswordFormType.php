<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de réinitialisation de mot de passe
 * 
 * Ce formulaire gère :
 * - La saisie du nouveau mot de passe avec confirmation
 * - La validation de la correspondance des mots de passe
 * - Les contraintes de sécurité sur la longueur du mot de passe
 * - L'interface utilisateur sécurisée pour la réinitialisation
 * - Les messages d'erreur personnalisés
 * 
 * Note : Ce formulaire est utilisé après réception du lien de réinitialisation
 * par email. Il n'est pas lié à une entité spécifique.
 */
class ResetPasswordFormType extends AbstractType
{
    /**
     * Construction du formulaire de réinitialisation de mot de passe
     * 
     * Cette méthode définit un formulaire sécurisé avec :
     * - Un champ répété pour le nouveau mot de passe (saisie + confirmation)
     * - Des contraintes de validation appropriées
     * - Des messages d'erreur personnalisés
     * - Des attributs HTML pour l'interface utilisateur
     * - Aucun champ mappé à une entité
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ nouveau mot de passe - Saisie répétée avec confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class, // Type de champ pour les deux saisies
                'first_options' => [
                    'constraints' => [
                        // Contrainte : champ obligatoire
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        // Contrainte : longueur du mot de passe (6-4096 caractères)
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Le mot de passe doit contenir au moins 12 caractères.',
                            'max' => 4096,
                        ]),
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
                'invalid_message' => 'Les mots de passe doivent correspondre.', // Message si non correspondance
            ])
        ;
    }
} 