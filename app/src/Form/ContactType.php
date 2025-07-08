<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire de contact pour les utilisateurs
 * 
 * Ce formulaire gère :
 * - La saisie de l'email de contact
 * - La saisie du message avec validation de longueur
 * - La protection CSRF pour la sécurité
 * - L'interface utilisateur avec placeholders et validation
 * - L'envoi du message via le bouton de soumission
 * 
 * Note : Ce formulaire n'est pas lié à une entité spécifique,
 * les données sont traitées directement dans le contrôleur.
 */
class ContactType extends AbstractType
{
    /**
     * Construction du formulaire de contact
     * 
     * Cette méthode définit un formulaire simple avec :
     * - Un champ email avec validation automatique
     * - Un champ message avec validation de longueur
     * - Un bouton de soumission stylisé
     * - Des attributs HTML pour l'accessibilité et l'UX
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email - Validation automatique du format email
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@email.com',
                    'autocomplete' => 'email' // Attribut pour les navigateurs
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir votre adresse e-mail.'
                    ]),
                    // Contrainte : format email valide
                    new Assert\Email([
                        'message' => 'Veuillez saisir une adresse e-mail valide.'
                    ])
                ]
            ])
            
            // Champ message - Zone de texte avec validation de longueur
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Tapez votre message ici...',
                    'rows' => 6, // Hauteur de la zone de texte
                    'minlength' => 10 // Validation HTML côté client
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir votre message.'
                    ]),
                    // Contrainte : longueur du message (10-2000 caractères)
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'Votre message doit contenir au moins {{ limit }} caractères.',
                        'max' => 2000,
                        'maxMessage' => 'Votre message ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            
            // Bouton de soumission - Stylisé avec Bootstrap
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg' // Classes Bootstrap
                ]
            ])
        ;
    }

    /**
     * Configuration des options du formulaire
     * 
     * Cette méthode définit :
     * - Aucune classe d'entité associée (formulaire standalone)
     * - La protection CSRF activée pour la sécurité
     * - Les paramètres de sécurité CSRF personnalisés
     * 
     * @param OptionsResolver $resolver Résolveur d'options Symfony
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas d'entité associée
            'csrf_protection' => true, // Protection CSRF activée
            'csrf_field_name' => '_token', // Nom du champ CSRF
            'csrf_token_id' => 'contact_form' // Identifiant du token CSRF
        ]);
    }
} 