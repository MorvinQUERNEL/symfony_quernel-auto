<?php

namespace App\Form;

use App\Entity\Messages;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de création et modification de messages
 * 
 * Ce formulaire gère :
 * - La saisie du sujet du message avec validation de longueur
 * - La saisie du contenu du message avec validation de longueur
 * - La date d'envoi automatiquement définie
 * - La sélection du destinataire parmi les utilisateurs
 * - La validation des données avec contraintes Symfony
 * - L'interface utilisateur pour la composition de messages
 * - Les relations avec les entités Messages et Users
 */
class MessagesType extends AbstractType
{
    /**
     * Construction du formulaire de message
     * 
     * Cette méthode définit tous les champs du formulaire avec :
     * - Le sujet du message avec validation de longueur
     * - Le contenu du message avec validation de longueur
     * - La date d'envoi automatiquement définie
     * - La sélection du destinataire avec label personnalisé
     * - Les contraintes de validation appropriées
     * - Les attributs HTML pour l'interface utilisateur
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ sujet - Texte avec validation de longueur
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Entrez le sujet du message',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le sujet est obligatoire',
                    ]),
                    // Contrainte : longueur maximale du sujet
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 50,
                        'maxMessage' => 'Le sujet ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            
            // Champ contenu - Zone de texte avec validation de longueur
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Entrez le contenu de votre message',
                    'class' => 'form-control',
                    'rows' => 5, // Hauteur de la zone de texte
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le contenu est obligatoire',
                    ]),
                    // Contrainte : longueur maximale du contenu
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Le contenu ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            
            // Champ date d'envoi - Date/heure automatiquement définie
            ->add('sendAt', DateTimeType::class, [
                'label' => 'Date d\'envoi',
                'widget' => 'single_text', // Utilise l'input datetime-local HTML5
                'attr' => [
                    'class' => 'form-control',
                ],
                'data' => new \DateTimeImmutable(), // Date/heure actuelle par défaut
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'La date d\'envoi est obligatoire',
                    ]),
                ],
            ])
            
            // Champ destinataire - Sélection d'entité avec label personnalisé
            ->add('recipient', EntityType::class, [
                'class' => Users::class, // Entité à sélectionner
                'choice_label' => function (Users $user) {
                    // Label personnalisé : Prénom Nom (email)
                    return $user->getFirstname() . ' ' . $user->getLastname() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Destinataire',
                'placeholder' => 'Choisissez un destinataire', // Option par défaut
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'Le destinataire est obligatoire',
                    ]),
                ],
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
            'data_class' => Messages::class, // Entité associée au formulaire
        ]);
    }
} 