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

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de création est obligatoire'
                    ])
                ]
            ])
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
            ->add('totalPrice', MoneyType::class, [
                'label' => 'Prix total',
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => 'Ex: 25000',
                    'class' => 'form-control',
                    'readonly' => true
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
            ->add('users', EntityType::class, [
                'label' => 'Client',
                'class' => Users::class,
                'choice_label' => function (Users $user) {
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
            ->add('vehicules', EntityType::class, [
                'label' => 'Véhicules',
                'class' => Vehicules::class,
                'choice_label' => function (Vehicules $vehicule) {
                    return $vehicule->getBrand() . ' ' . $vehicule->getModel() . ' (' . $vehicule->getYear()->format('Y') . ')';
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    /**
     * Construit un formulaire simplifié pour l'achat de véhicule
     * Ne contient que les champs de livraison visibles à l'utilisateur
     */
    public static function buildPurchaseForm(FormBuilderInterface $builder): void
    {
        $builder
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
} 