<?php

namespace App\Controller;

use App\Entity\Vehicules;
use App\Entity\Pictures;
use App\Form\VehiculeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vehicules')]
class VehiculeController extends AbstractController
{
    #[Route('/', name: 'app_vehicules_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $vehicules = $entityManager
                ->getRepository(Vehicules::class)
                ->findBySearch($search);
        } else {
            $vehicules = $entityManager
                ->getRepository(Vehicules::class)
                ->findAll();
        }

        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_vehicules_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $vehicule = new Vehicules();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload des images
            $picturesData = $form->get('pictures')->getData();
            
            foreach ($picturesData as $picture) {
                $imageFile = $form->get('pictures')->get($picturesData->indexOf($picture))->get('imageFile')->getData();
                
                if ($imageFile && $imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Déplacer le fichier vers le répertoire uploads
                    try {
                        $imageFile->move(
                            $this->getParameter('vehicules_directory'),
                            $newFilename
                        );

                        // Mettre à jour le nom du fichier dans l'entité Pictures
                        $picture->setName($newFilename);
                        $picture->setVehicules($vehicule);
                        
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    }
                } else {
                    // Si pas de fichier, supprimer cette entité Pictures
                    $vehicule->removePicture($picture);
                }
            }

            $entityManager->persist($vehicule);
            $entityManager->flush();

            $this->addFlash('success', 'Le véhicule a été ajouté avec succès !');
            return $this->redirectToRoute('app_vehicules_index');
        }

        return $this->render('vehicule/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicules_show', methods: ['GET'])]
    public function show(Vehicules $vehicule): Response
    {
        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehicules_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Vehicules $vehicule, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload des nouvelles images
            $picturesData = $form->get('pictures')->getData();
            
            foreach ($picturesData as $picture) {
                $imageFile = $form->get('pictures')->get($picturesData->indexOf($picture))->get('imageFile')->getData();
                
                if ($imageFile && $imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Déplacer le fichier vers le répertoire uploads
                    try {
                        $imageFile->move(
                            $this->getParameter('vehicules_directory'),
                            $newFilename
                        );

                        // Mettre à jour le nom du fichier dans l'entité Pictures
                        $picture->setName($newFilename);
                        $picture->setVehicules($vehicule);
                        
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    }
                } else {
                    // Si pas de fichier et que l'image n'a pas déjà un nom, supprimer cette entité Pictures
                    if (!$picture->getName()) {
                        $vehicule->removePicture($picture);
                    }
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le véhicule a été modifié avec succès !');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        return $this->render('vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicules_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Vehicules $vehicule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vehicule);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le véhicule a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_vehicules_index');
    }
} 