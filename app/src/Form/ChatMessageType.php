<?php

namespace App\Form;

use App\Entity\Messages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de message de chat
 * 
 * Ce formulaire gère :
 * - La saisie de messages de chat en temps réel
 * - La validation de la longueur des messages
 * - L'interface utilisateur optimisée pour le chat
 * - Les contraintes de validation appropriées
 * - Les relations avec l'entité Messages
 * 
 * Note : Ce formulaire est conçu pour être intégré dans une interface
 * de chat en temps réel avec une zone de saisie compacte.
 */
class ChatMessageType extends AbstractType
{
    /**
     * Construction du formulaire de message de chat
     * 
     * Cette méthode définit un formulaire optimisé pour le chat avec :
     * - Une zone de texte compacte pour la saisie rapide
     * - Des contraintes de validation pour la longueur
     * - Des attributs HTML spécifiques au chat
     * - Une interface utilisateur minimaliste
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ contenu du message - Zone de texte optimisée pour le chat
            ->add('content', TextareaType::class, [
                'label' => false, // Pas de label pour un design plus compact
                'attr' => [
                    'placeholder' => 'Tapez votre message...', // Placeholder informatif
                    'class' => 'chat-message-input', // Classe CSS spécifique au chat
                    'rows' => 1, // Hauteur minimale pour un design compact
                    'maxlength' => 255, // Limite HTML côté client
                ],
                'constraints' => [
                    // Contrainte : message non vide
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le message ne peut pas être vide',
                    ]),
                    // Contrainte : longueur maximale du message
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Le message ne peut pas dépasser {{ limit }} caractères',
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