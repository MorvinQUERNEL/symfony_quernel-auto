<?php

namespace App\Form;

use App\Entity\Orders;
use App\Entity\Users;
use App\Entity\Vehicules;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Formulaire de gestion des commandes (administration et achat)
 * 
 * Ce formulaire gère :
 * - Les informations de base de la commande (date, statut, prix)
 * - Les informations de livraison complètes
 * - La sélection du client et des véhicules
 * - La validation des données avec contraintes Symfony
 * - Un formulaire simplifié pour l'achat client
 * - Les relations avec les entités Users et Vehicules
 */
class OrderType extends AbstractType
{
    /**
     * Construction du formulaire complet de commande (administration)
     * 
     * Cette méthode définit tous les champs du formulaire avec :
     * - Les informations de base de la commande
     * - Les informations de livraison avec validation
     * - La sélection des entités liées (client, véhicules)
     * - Les contraintes de validation appropriées
     * - Les attributs HTML pour l'interface utilisateur
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ date de création - Lecture seule, générée automatiquement
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text', // Utilise l'input datetime-local HTML5
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true // Lecture seule, générée automatiquement
                ],
                'required' => false, // Optionnel car généré automatiquement
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de création est obligatoire'
                    ])
                ]
            ])
            
            // Champ statut de commande - Liste déroulante avec tous les statuts possibles
            ->add('orderStatus', ChoiceType::class, [
                'label' => 'Statut de la commande',
                'choices' => [
                    'En attente' => 'pending',
                    'Payée' => 'paid',
                    'Expirée' => 'expired',
                    'Annulée' => 'cancelled',
                    'En cours de traitement' => 'processing',
                    'Expédiée' => 'shipped',
                    'Livrée' => 'delivered'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le statut de la commande est obligatoire'
                    ])
                ]
            ])
            
            // Champ prix total - Montant en euros, lecture seule
            ->add('totalPrice', MoneyType::class, [
                'label' => 'Prix total',
                'currency' => 'EUR', // Devise en euros
                'attr' => [
                    'placeholder' => 'Ex: 25000',
                    'class' => 'form-control',
                    'readonly' => true // Lecture seule, calculé automatiquement
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix total est obligatoire'
                    ]),
                    new Positive([
                        'message' => 'Le prix total doit être positif'
                    ])
                ]
            ])
            
            // Champ adresse de livraison - Texte avec validation de longueur
            ->add('deliveryAdress', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => [
                    'placeholder' => 'Ex: 123 Rue de la Paix',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse de livraison est obligatoire'
                    ]),
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
                    new NotBlank([
                        'message' => 'La ville de livraison est obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La ville doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ code postal - Nombre entier avec validation regex
            ->add('deliveryPostalCode', IntegerType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Ex: 75001',
                    'class' => 'form-control',
                    'min' => 1000, // Validation HTML côté client
                    'max' => 99999
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire'
                    ]),
                    new Positive([
                        'message' => 'Le code postal doit être positif'
                    ]),
                    new Regex([
                        'pattern' => '/^\d{5}$/', // Exactement 5 chiffres
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
                    new NotBlank([
                        'message' => 'Le pays de livraison est obligatoire'
                    ])
                ]
            ])
            
            // Champ client - Sélection d'entité avec label personnalisé
            ->add('users', EntityType::class, [
                'label' => 'Client',
                'class' => Users::class, // Entité à sélectionner
                'choice_label' => function (Users $user) {
                    // Label personnalisé : Prénom Nom (email)
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')';
                },
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le client est obligatoire'
                    ])
                ]
            ])
            
            // Champ véhicules - Sélection multiple d'entités
            ->add('vehicules', EntityType::class, [
                'label' => 'Véhicules',
                'class' => Vehicules::class, // Entité à sélectionner
                'choice_label' => function (Vehicules $vehicule) {
                    // Label personnalisé : Marque Modèle (Année)
                    return $vehicule->getBrand() . ' ' . $vehicule->getModel() . ' (' . $vehicule->getYear()->format('Y') . ')';
                },
                'multiple' => true, // Sélection multiple autorisée
                'expanded' => false, // Liste déroulante (pas de checkboxes)
                'required' => false, // Optionnel
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    /**
     * Construction d'un formulaire simplifié pour l'achat de véhicule
     * 
     * Cette méthode statique crée un formulaire simplifié contenant uniquement
     * les champs de livraison visibles à l'utilisateur final lors de l'achat.
     * Les autres champs (client, prix, statut) sont gérés automatiquement.
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     */
    public static function buildPurchaseForm(FormBuilderInterface $builder): void
    {
        $builder
            // Champ adresse de livraison - Même configuration que le formulaire complet
            ->add('deliveryAdress', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => [
                    'placeholder' => 'Ex: 123 Rue de la Paix',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse de livraison est obligatoire'
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ ville de livraison - Même configuration que le formulaire complet
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville de livraison',
                'attr' => [
                    'placeholder' => 'Ex: Paris',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La ville de livraison est obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La ville doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            // Champ code postal - Même configuration que le formulaire complet
            ->add('deliveryPostalCode', IntegerType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Ex: 75001',
                    'class' => 'form-control',
                    'min' => 1000,
                    'max' => 99999
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire'
                    ]),
                    new Positive([
                        'message' => 'Le code postal doit être positif'
                    ]),
                    new Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Le code postal doit contenir exactement 5 chiffres'
                    ])
                ]
            ])
            
            // Champ pays de livraison - Même configuration que le formulaire complet
            ->add('deliveryCountry', CountryType::class, [
                'label' => 'Pays de livraison',
                'preferred_choices' => ['FR' => 'France', 'BE' => 'Belgique', 'CH' => 'Suisse', 'CA' => 'Canada'],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
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