<?php

namespace App\Form;

use App\Entity\Vehicules;
use App\Form\PictureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

/**
 * Formulaire de gestion des véhicules (ajout/modification)
 * 
 * Ce formulaire gère :
 * - Les informations de base du véhicule (marque, modèle, année)
 * - Les caractéristiques techniques (kilométrage, carburant, transmission)
 * - Les informations de vente (prix, statut)
 * - L'upload et gestion des images du véhicule
 * - La validation complète des données avec contraintes Symfony
 * - L'interface utilisateur avec placeholders et classes CSS
 */
class VehiculeType extends AbstractType
{
    /**
     * Construction du formulaire de véhicule
     * 
     * Cette méthode définit tous les champs du formulaire avec :
     * - Les types de champs appropriés pour chaque donnée
     * - Les contraintes de validation (obligatoire, longueur, valeurs positives)
     * - Les choix prédéfinis pour les listes déroulantes
     * - Les attributs HTML pour l'interface utilisateur
     * - La gestion des images avec CollectionType
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ marque - Texte avec validation de longueur
            ->add('brand', TextType::class, [
                'label' => 'Marque',
                'attr' => [
                    'placeholder' => 'Ex: Audi, BMW, Mercedes...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La marque est obligatoire'
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La marque ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ modèle - Texte avec validation de longueur
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'attr' => [
                    'placeholder' => 'Ex: A3, X5, Classe C...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le modèle est obligatoire'
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le modèle ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ année - Sélecteur de date (widget single_text pour HTML5)
            ->add('year', DateType::class, [
                'label' => 'Année',
                'widget' => 'single_text', // Utilise l'input date HTML5
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'année est obligatoire'
                    ])
                ]
            ])
            
            // Champ kilométrage - Nombre entier positif
            ->add('mileage', IntegerType::class, [
                'label' => 'Kilométrage',
                'attr' => [
                    'placeholder' => 'Ex: 50000',
                    'class' => 'form-control',
                    'min' => 0 // Attribut HTML pour validation côté client
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le kilométrage est obligatoire'
                    ]),
                    new Positive([
                        'message' => 'Le kilométrage doit être positif'
                    ])
                ]
            ])
            
            // Champ type de carburant - Liste déroulante avec choix prédéfinis
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Type de carburant',
                'choices' => [
                    'Essence' => 'Essence',
                    'Diesel' => 'Diesel',
                    'Hybride' => 'Hybride',
                    'Électrique' => 'Électrique',
                    'GPL' => 'GPL',
                    'Éthanol' => 'Éthanol'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type de carburant est obligatoire'
                    ])
                ]
            ])
            
            // Champ transmission - Liste déroulante avec choix prédéfinis
            ->add('trasmission', ChoiceType::class, [
                'label' => 'Transmission',
                'choices' => [
                    'Manuelle' => 'Manuelle',
                    'Automatique' => 'Automatique',
                    'Semi-automatique' => 'Semi-automatique'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La transmission est obligatoire'
                    ])
                ]
            ])
            
            // Champ couleur - Texte avec validation de longueur
            ->add('color', TextType::class, [
                'label' => 'Couleur',
                'attr' => [
                    'placeholder' => 'Ex: Blanc, Noir, Gris...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La couleur est obligatoire'
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La couleur ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ nombre de portes - Liste déroulante avec validation de plage
            ->add('doorCount', ChoiceType::class, [
                'label' => 'Nombre de portes',
                'choices' => [
                    '2 portes' => 2,
                    '3 portes' => 3,
                    '4 portes' => 4,
                    '5 portes' => 5
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nombre de portes est obligatoire'
                    ]),
                    new Range([
                        'min' => 2,
                        'max' => 5,
                        'notInRangeMessage' => 'Le nombre de portes doit être entre {{ min }} et {{ max }}'
                    ])
                ]
            ])
            
            // Champ description - Zone de texte optionnelle
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false, // Champ optionnel
                'attr' => [
                    'placeholder' => 'Description détaillée du véhicule...',
                    'class' => 'form-control',
                    'rows' => 4 // Hauteur de la zone de texte
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ statut - Liste déroulante optionnelle
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => false, // Champ optionnel
                'choices' => [
                    'Disponible' => 'Disponible',
                    'Vendu' => 'Vendu',
                    'En cours de vente' => 'En cours de vente',
                    'En réparation' => 'En réparation',
                    'Réservé' => 'Réservé'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Champ prix de vente - Montant en euros avec validation positive
            ->add('salePrice', MoneyType::class, [
                'label' => 'Prix de vente',
                'currency' => 'EUR', // Devise en euros
                'attr' => [
                    'placeholder' => 'Ex: 25000',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix de vente est obligatoire'
                    ]),
                    new Positive([
                        'message' => 'Le prix de vente doit être positif'
                    ])
                ]
            ])
            
            // Champ images - Collection de formulaires PictureType
            ->add('pictures', CollectionType::class, [
                'label' => 'Images',
                'entry_type' => PictureType::class, // Type de formulaire pour chaque image
                'entry_options' => [
                    'label' => false // Pas de label pour chaque entrée
                ],
                'allow_add' => true, // Permet d'ajouter de nouvelles images
                'allow_delete' => true, // Permet de supprimer des images
                'prototype' => true, // Génère un prototype pour JavaScript
                'by_reference' => false, // Force l'utilisation des setters
                'required' => false // Collection optionnelle
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
            'data_class' => Vehicules::class, // Entité associée au formulaire
        ]);
    }
} 