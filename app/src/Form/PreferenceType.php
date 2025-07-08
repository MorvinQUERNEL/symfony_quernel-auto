<?php

namespace App\Form;

use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de gestion des préférences de véhicules
 * 
 * Ce formulaire gère :
 * - La saisie des critères de recherche de véhicules
 * - Les préférences de marque et modèle
 * - Les critères de prix et d'année
 * - Le type de carburant préféré
 * - Tous les champs sont optionnels pour la flexibilité
 * - L'interface utilisateur pour la personnalisation des préférences
 * - Les relations avec l'entité Preference
 */
class PreferenceType extends AbstractType
{
    /**
     * Construction du formulaire de préférences
     * 
     * Cette méthode définit les critères de recherche avec :
     * - Des champs de texte pour marque et modèle
     * - Un champ de date pour l'année minimale
     * - Un champ numérique pour le prix maximum
     * - Une liste déroulante pour le type de carburant
     * - Tous les champs sont optionnels pour permettre des recherches flexibles
     * - Des attributs HTML pour l'interface utilisateur
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ marque préférée - Texte optionnel
            ->add('prefereneceBrand', TextType::class, [
                'label' => 'Marque préférée',
                'required' => false, // Champ optionnel
            ])
            
            // Champ modèle préféré - Texte optionnel
            ->add('preferenceModel', TextType::class, [
                'label' => 'Modèle préféré',
                'required' => false, // Champ optionnel
            ])
            
            // Champ année minimale - Date optionnelle
            ->add('minYear', DateType::class, [
                'label' => 'Année minimale',
                'widget' => 'single_text', // Utilise l'input date HTML5
                'required' => false, // Champ optionnel
                'html5' => true, // Utilise les contrôles HTML5 natifs
            ])
            
            // Champ prix maximum - Nombre entier optionnel
            ->add('maxPrice', IntegerType::class, [
                'label' => 'Prix maximum',
                'required' => false, // Champ optionnel
            ])
            
            // Champ type de carburant - Liste déroulante optionnelle
            ->add('preferenceFuelType', ChoiceType::class, [
                'label' => 'Type de carburant',
                'choices' => [
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                    'GPL' => 'gpl',
                    'Autre' => 'autre',
                ],
                'required' => false, // Champ optionnel
                'placeholder' => 'Sélectionnez', // Option par défaut
            ]);
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
            'data_class' => Preference::class, // Entité associée au formulaire
        ]);
    }
} 