<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de modification du profil utilisateur
 * 
 * Ce formulaire gère :
 * - La modification des informations personnelles (nom, prénom)
 * - La mise à jour des informations d'adresse
 * - La modification du numéro de téléphone
 * - La validation des données avec contraintes HTML5
 * - L'interface utilisateur pour l'édition du profil
 * 
 * Note : Ce formulaire ne gère pas la modification de l'email
 * ni du mot de passe (formulaires séparés).
 */
class ProfileType extends AbstractType
{
    /**
     * Construction du formulaire de profil
     * 
     * Cette méthode définit les champs modifiables du profil avec :
     * - Les informations personnelles (nom, prénom)
     * - Les informations d'adresse complètes
     * - Le numéro de téléphone avec validation
     * - Les attributs HTML pour la validation côté client
     * - Tous les champs sont obligatoires
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ prénom - Texte obligatoire
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true, // Champ obligatoire
            ])
            
            // Champ nom - Texte obligatoire
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true, // Champ obligatoire
            ])
            
            // Champ adresse - Texte obligatoire
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => true, // Champ obligatoire
            ])
            
            // Champ ville - Texte obligatoire
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => true, // Champ obligatoire
            ])
            
            // Champ code postal - Nombre avec validation de plage
            ->add('postalCode', NumberType::class, [
                'label' => 'Code postal',
                'required' => true, // Champ obligatoire
                'attr' => [
                    'min' => 1000, // Code postal minimum (validation HTML)
                    'max' => 99999, // Code postal maximum (validation HTML)
                ],
            ])
            
            // Champ pays - Texte obligatoire
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => true, // Champ obligatoire
            ])
            
            // Champ numéro de téléphone - Type téléphone avec validation regex
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true, // Champ obligatoire
                'attr' => [
                    'pattern' => '[0-9]{10}', // Exactement 10 chiffres (validation HTML)
                    'placeholder' => '0123456789', // Exemple de format
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
            'data_class' => Users::class, // Entité associée au formulaire
        ]);
    }
} 