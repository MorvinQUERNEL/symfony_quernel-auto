<?php

namespace App\Service;

use App\Entity\Pictures;
use App\Entity\Vehicules;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service de gestion de l'upload et suppression d'images de véhicules
 * 
 * Ce service gère :
 * - L'upload sécurisé d'images de véhicules
 * - La génération de noms de fichiers uniques et sécurisés
 * - La suppression d'images du système de fichiers
 * - L'association des images aux véhicules
 * - La gestion des extensions de fichiers
 * 
 * Assure la sécurité des uploads en générant des noms de fichiers
 * uniques et en utilisant le slugger Symfony pour éviter les conflits.
 */
class PictureUploadService
{
    private SluggerInterface $slugger;
    private string $vehiculesDirectory;

    /**
     * Constructeur du service d'upload d'images
     * 
     * @param SluggerInterface $slugger Service de génération de slugs Symfony
     * @param string $vehiculesDirectory Chemin vers le répertoire d'upload des véhicules
     */
    public function __construct(SluggerInterface $slugger, string $vehiculesDirectory)
    {
        $this->slugger = $slugger;
        $this->vehiculesDirectory = $vehiculesDirectory;
    }

    /**
     * Gère l'upload d'une image pour un véhicule
     * 
     * Cette méthode effectue l'upload complet d'une image :
     * - Génère un nom de fichier sécurisé et unique
     * - Déplace le fichier vers le répertoire d'upload
     * - Met à jour l'entité Pictures avec le nouveau nom
     * - Associe l'image au véhicule correspondant
     * 
     * Le nom de fichier généré suit le format :
     * {nom-original-slug}-{id-unique}.{extension}
     * 
     * @param UploadedFile $imageFile Fichier uploadé par l'utilisateur
     * @param Pictures $picture Entité Pictures à mettre à jour
     * @param Vehicules $vehicule Véhicule auquel associer l'image
     * 
     * @throws \Exception En cas d'erreur lors de l'upload ou du déplacement du fichier
     */
    public function uploadPicture(UploadedFile $imageFile, Pictures $picture, Vehicules $vehicule): void
    {
        // Récupération du nom original du fichier (sans extension)
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Génération d'un nom de fichier sécurisé avec le slugger
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // Création d'un nom unique avec timestamp et extension
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        // Déplacement du fichier vers le répertoire d'upload des véhicules
        $imageFile->move($this->vehiculesDirectory, $newFilename);

        // Mise à jour de l'entité Pictures avec le nouveau nom de fichier
        $picture->setName($newFilename);
        
        // Association de l'image au véhicule
        $picture->setVehicules($vehicule);
    }

    /**
     * Supprime une image du système de fichiers
     * 
     * Cette méthode supprime physiquement le fichier image du serveur.
     * Elle vérifie d'abord l'existence du fichier avant de le supprimer
     * pour éviter les erreurs si le fichier n'existe plus.
     * 
     * @param Pictures $picture Entité Pictures contenant les informations du fichier
     * 
     * @throws \Exception En cas d'erreur lors de la suppression du fichier
     */
    public function deletePicture(Pictures $picture): void
    {
        // Construction du chemin complet vers le fichier
        $filename = $this->vehiculesDirectory . '/' . $picture->getName();
        
        // Vérification de l'existence du fichier avant suppression
        if (file_exists($filename)) {
            unlink($filename); // Suppression du fichier du système de fichiers
        }
    }
} 