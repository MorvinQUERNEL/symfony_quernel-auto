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

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('year', DateType::class, [
                'label' => 'Année',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'année est obligatoire'
                    ])
                ]
            ])
            ->add('mileage', IntegerType::class, [
                'label' => 'Kilométrage',
                'attr' => [
                    'placeholder' => 'Ex: 50000',
                    'class' => 'form-control',
                    'min' => 0
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
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée du véhicule...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => false,
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
            ->add('salePrice', MoneyType::class, [
                'label' => 'Prix de vente',
                'currency' => 'EUR',
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
            ->add('pictures', CollectionType::class, [
                'label' => 'Images',
                'entry_type' => PictureType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicules::class,
        ]);
    }
} 