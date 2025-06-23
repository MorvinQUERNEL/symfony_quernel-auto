<?php

namespace App\Service;

use App\Entity\Pictures;
use App\Entity\Vehicules;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PictureUploadService
{
    private SluggerInterface $slugger;
    private string $vehiculesDirectory;

    public function __construct(SluggerInterface $slugger, string $vehiculesDirectory)
    {
        $this->slugger = $slugger;
        $this->vehiculesDirectory = $vehiculesDirectory;
    }

    /**
     * Gère l'upload d'une image pour un véhicule
     * 
     * @throws \Exception
     */
    public function uploadPicture(UploadedFile $imageFile, Pictures $picture, Vehicules $vehicule): void
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        // Déplacer le fichier vers le répertoire uploads
        $imageFile->move($this->vehiculesDirectory, $newFilename);

        // Mettre à jour le nom du fichier dans l'entité Pictures
        $picture->setName($newFilename);
        $picture->setVehicules($vehicule);
    }

    /**
     * Supprime une image du système de fichiers
     */
    public function deletePicture(Pictures $picture): void
    {
        $filename = $this->vehiculesDirectory . '/' . $picture->getName();
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
} 