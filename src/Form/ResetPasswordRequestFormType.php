<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Formulaire de demande de réinitialisation de mot de passe
 * 
 * Ce formulaire gère :
 * - La saisie de l'email pour recevoir le lien de réinitialisation
 * - La validation du format email
 * - L'interface utilisateur simple pour la demande
 * - Les messages d'erreur personnalisés
 * 
 * Note : Ce formulaire est la première étape du processus de réinitialisation.
 * Il envoie un email avec un lien pour accéder au formulaire de réinitialisation.
 */
class ResetPasswordRequestFormType extends AbstractType
{
    /**
     * Construction du formulaire de demande de réinitialisation
     * 
     * Cette méthode définit un formulaire simple avec :
     * - Un champ email avec validation automatique
     * - Des contraintes de validation appropriées
     * - Des attributs HTML pour l'interface utilisateur
     * - Aucun champ mappé à une entité
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email - Validation automatique du format email
            ->add('email', EmailType::class, [
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email',
                    ]),
                    // Contrainte : format email valide
                    new Email([
                        'message' => 'Veuillez entrer un email valide',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre email'
                ],
                'label' => 'Email'
            ])
        ;
    }
} 