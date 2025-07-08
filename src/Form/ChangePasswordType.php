<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de changement de mot de passe
 * 
 * Ce formulaire gère :
 * - La saisie du mot de passe actuel pour vérification
 * - La saisie du nouveau mot de passe avec confirmation
 * - La validation de la correspondance des mots de passe
 * - Les contraintes de sécurité sur la longueur du mot de passe
 * - L'interface utilisateur sécurisée pour le changement de mot de passe
 * 
 * Note : Ce formulaire n'est pas lié à une entité spécifique,
 * les données sont traitées directement dans le contrôleur.
 */
class ChangePasswordType extends AbstractType
{
    /**
     * Construction du formulaire de changement de mot de passe
     * 
     * Cette méthode définit un formulaire sécurisé avec :
     * - Un champ pour le mot de passe actuel (vérification)
     * - Un champ répété pour le nouveau mot de passe (saisie + confirmation)
     * - Des contraintes de validation appropriées
     * - Des messages d'erreur personnalisés
     * - Aucun champ mappé à une entité
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ mot de passe actuel - Vérification avant changement
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false, // Ne pas mapper à une entité
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel',
                    ]),
                ],
            ])
            
            // Champ nouveau mot de passe - Saisie répétée avec confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class, // Type de champ pour les deux saisies
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
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
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.', // Message si non correspondance
            ])
        ;
    }

    /**
     * Configuration des options du formulaire
     * 
     * Cette méthode définit :
     * - Aucune classe d'entité associée (formulaire standalone)
     * - Les options par défaut pour la validation
     * 
     * @param OptionsResolver $resolver Résolveur d'options Symfony
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Aucune configuration spéciale nécessaire
        ]);
    }
} 