<?php

namespace App\Form;

use App\Entity\Pictures;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Formulaire de gestion des images (upload et description)
 * 
 * Ce formulaire gère :
 * - L'upload de fichiers images avec validation de type et taille
 * - La saisie d'une description optionnelle pour l'image
 * - La validation des formats d'image supportés (JPG, PNG, GIF, WebP)
 * - La limitation de taille des fichiers (10MB max)
 * - L'interface utilisateur pour l'upload d'images
 * - Les relations avec l'entité Pictures
 */
class PictureType extends AbstractType
{
    /**
     * Construction du formulaire d'image
     * 
     * Cette méthode définit les champs du formulaire avec :
     * - Un champ d'upload de fichier avec validation stricte
     * - Un champ de description optionnelle
     * - Des contraintes de validation pour les fichiers
     * - Des attributs HTML pour l'interface utilisateur
     * - Des messages d'aide pour guider l'utilisateur
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ upload d'image - Fichier avec validation stricte
            ->add('imageFile', FileType::class, [
                'label' => 'Nouvelle image',
                'mapped' => false, // Ne pas mapper directement à l'entité
                'required' => false, // Champ optionnel (permet de conserver l'image existante)
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    // Contrainte : validation du fichier uploadé
                    new File([
                        'maxSize' => '10M', // Taille maximale 10MB
                        'mimeTypes' => [
                            'image/jpeg', // Format JPG
                            'image/png',  // Format PNG
                            'image/gif',  // Format GIF
                            'image/webp'  // Format WebP
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF, WebP)'
                    ])
                ],
                'help' => 'Laissez vide pour conserver l\'image actuelle' // Message d'aide
            ])
            
            // Champ description - Texte optionnel avec validation de longueur
            ->add('description', TextType::class, [
                'label' => 'Description de l\'image',
                'required' => false, // Champ optionnel
                'attr' => [
                    'placeholder' => 'Description optionnelle de l\'image',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    // Contrainte : longueur maximale de la description
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
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
            'data_class' => Pictures::class, // Entité associée au formulaire
        ]);
    }
} 