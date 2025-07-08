<?php

namespace App\Form;

use App\Entity\Orders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Formulaire simplifié pour l'achat de véhicule
 * 
 * Ce formulaire gère :
 * - La saisie des informations de livraison pour l'achat
 * - Les champs essentiels pour finaliser une commande
 * - La validation des données d'adresse avec contraintes strictes
 * - L'interface utilisateur simplifiée pour l'achat
 * - Les relations avec l'entité Orders
 * 
 * Note : Ce formulaire ne contient que les champs de livraison
 * visibles à l'utilisateur final lors de l'achat.
 */
class PurchaseOrderType extends AbstractType
{
    /**
     * Construction du formulaire d'achat simplifié
     * 
     * Cette méthode définit uniquement les champs de livraison avec :
     * - L'adresse de livraison avec validation de longueur
     * - La ville de livraison avec validation de longueur
     * - Le code postal avec validation regex stricte
     * - Le pays de livraison avec pays préférés
     * - Des contraintes de validation appropriées
     * - Des attributs HTML pour l'interface utilisateur
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ adresse de livraison - Texte avec validation de longueur
            ->add('deliveryAdress', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => [
                    'placeholder' => 'Ex: 123 Rue de la Paix',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'L\'adresse de livraison est obligatoire'
                    ]),
                    // Contrainte : longueur de l'adresse (5-100 caractères)
                    new Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ ville de livraison - Texte avec validation de longueur
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville de livraison',
                'attr' => [
                    'placeholder' => 'Ex: Paris',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'La ville de livraison est obligatoire'
                    ]),
                    // Contrainte : longueur de la ville (2-100 caractères)
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La ville doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ code postal - Nombre entier avec validation regex stricte
            ->add('deliveryPostalCode', IntegerType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Ex: 75001',
                    'class' => 'form-control',
                    'min' => 1000, // Validation HTML côté client
                    'max' => 99999
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire'
                    ]),
                    // Contrainte : valeur positive
                    new Positive([
                        'message' => 'Le code postal doit être positif'
                    ]),
                    // Contrainte : exactement 5 chiffres
                    new Regex([
                        'pattern' => '/^\d{5}$/', // Regex pour 5 chiffres exactement
                        'message' => 'Le code postal doit contenir exactement 5 chiffres'
                    ])
                ]
            ])
            
            // Champ pays de livraison - Liste déroulante avec pays préférés
            ->add('deliveryCountry', CountryType::class, [
                'label' => 'Pays de livraison',
                'preferred_choices' => ['FR' => 'France', 'BE' => 'Belgique', 'CH' => 'Suisse', 'CA' => 'Canada'],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    // Contrainte : champ obligatoire
                    new NotBlank([
                        'message' => 'Le pays de livraison est obligatoire'
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
            'data_class' => Orders::class, // Entité associée au formulaire
        ]);
    }
} 