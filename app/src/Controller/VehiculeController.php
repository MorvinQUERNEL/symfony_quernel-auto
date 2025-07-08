<?php

namespace App\Controller;

use App\Entity\Vehicules;
use App\Entity\Pictures;
use App\Entity\Orders;
use App\Form\VehiculeType;
use App\Service\PictureUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Stripe\StripeClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contrôleur gérant les véhicules d'occasion
 * 
 * Ce contrôleur gère :
 * - L'affichage de la liste des véhicules avec recherche
 * - L'ajout, modification et suppression de véhicules (admin)
 * - L'affichage détaillé d'un véhicule
 * - Le processus d'achat de véhicules
 * - L'upload et gestion des images de véhicules
 * - L'intégration avec Stripe pour les paiements
 */
#[Route('/vehicules')]
class VehiculeController extends AbstractController
{
    private StripeClient $stripeClient;
    private string $stripePublicKey;
    private LoggerInterface $logger;
    private PictureUploadService $pictureUploadService;

    /**
     * Constructeur avec injection des dépendances
     * 
     * @param StripeClient $stripeClient Client Stripe pour les paiements
     * @param string $stripePublicKey Clé publique Stripe
     * @param LoggerInterface $logger Service de logging
     * @param PictureUploadService $pictureUploadService Service d'upload d'images
     */
    public function __construct(
        StripeClient $stripeClient,
        string $stripePublicKey,
        LoggerInterface $logger,
        PictureUploadService $pictureUploadService
    ) {
        $this->stripeClient = $stripeClient;
        $this->stripePublicKey = $stripePublicKey;
        $this->logger = $logger;
        $this->pictureUploadService = $pictureUploadService;
    }

    /**
     * Liste des véhicules avec fonctionnalité de recherche
     * 
     * Cette méthode gère :
     * - L'affichage de tous les véhicules avec leurs images
     * - La recherche par marque et modèle
     * - Le rendu de la page de liste avec filtres
     * 
     * @param Request $request Requête HTTP contenant les paramètres de recherche
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Page de liste des véhicules
     */
    #[Route('/', name: 'app_vehicules_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération du terme de recherche depuis l'URL
        $search = $request->query->get('search', '');
        
        // Recherche conditionnelle selon la présence d'un terme de recherche
        if ($search) {
            $vehicules = $entityManager
                ->getRepository(Vehicules::class)
                ->findBySearch($search);
        } else {
            $vehicules = $entityManager
                ->getRepository(Vehicules::class)
                ->findAllWithPictures();
        }

        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
            'search' => $search,
        ]);
    }

    /**
     * Ajout d'un nouveau véhicule (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire d'ajout de véhicule
     * - Le traitement du formulaire avec validation
     * - L'upload et traitement des images
     * - La persistance du véhicule en base de données
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Formulaire d'ajout ou redirection après succès
     */
    #[Route('/new', name: 'app_vehicules_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicule = new Vehicules();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des images uploadées
            $picturesData = $form->get('pictures')->getData();
            
            foreach ($picturesData as $picture) {
                $imageFile = $form->get('pictures')->get($picturesData->indexOf($picture))->get('imageFile')->getData();
                
                if ($imageFile && $imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    try {
                        // Upload et traitement de l'image
                        $this->pictureUploadService->uploadPicture($imageFile, $picture, $vehicule);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                        $this->logger->error('Erreur upload image', ['error' => $e->getMessage()]);
                    }
                } else {
                    // Suppression de l'entité Pictures si aucun fichier n'est fourni
                    $vehicule->removePicture($picture);
                }
            }

            // Persistance du véhicule en base de données
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

    /**
     * Affichage détaillé d'un véhicule
     * 
     * Cette méthode gère :
     * - L'affichage des informations complètes d'un véhicule
     * - L'affichage des images du véhicule
     * - Le rendu de la page de détail
     * 
     * @param Vehicules $vehicule Véhicule à afficher (injection automatique par Doctrine)
     * @return Response Page de détail du véhicule
     */
    #[Route('/{id}', name: 'app_vehicules_show', methods: ['GET'])]
    public function show(Vehicules $vehicule): Response
    {
        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    /**
     * Modification d'un véhicule existant (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de modification
     * - Le traitement des modifications avec validation
     * - La gestion des nouvelles images et suppression des anciennes
     * - La mise à jour du véhicule en base de données
     * 
     * @param Request $request Requête HTTP
     * @param Vehicules $vehicule Véhicule à modifier
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Formulaire de modification ou redirection après succès
     */
    #[Route('/{id}/edit', name: 'app_vehicules_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Vehicules $vehicule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des nouvelles images
            $picturesData = $form->get('pictures')->getData();
            
            foreach ($picturesData as $picture) {
                $imageFile = $form->get('pictures')->get($picturesData->indexOf($picture))->get('imageFile')->getData();
                
                if ($imageFile && $imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    try {
                        // Suppression de l'ancienne image si elle existe
                        if ($picture->getName()) {
                            $this->pictureUploadService->deletePicture($picture);
                        }
                        // Upload de la nouvelle image
                        $this->pictureUploadService->uploadPicture($imageFile, $picture, $vehicule);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                        $this->logger->error('Erreur upload image', ['error' => $e->getMessage()]);
                    }
                } else {
                    // Suppression de l'entité Pictures si aucun fichier et pas d'image existante
                    if (!$picture->getName()) {
                        $vehicule->removePicture($picture);
                    }
                }
            }

            // Mise à jour en base de données
            $entityManager->flush();

            $this->addFlash('success', 'Le véhicule a été modifié avec succès !');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        return $this->render('vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    /**
     * Suppression d'un véhicule (réservé aux administrateurs)
     * 
     * Cette méthode gère :
     * - La validation du token CSRF
     * - La suppression des images du système de fichiers
     * - La suppression du véhicule de la base de données
     * - La gestion des erreurs de suppression
     * 
     * @param Request $request Requête HTTP
     * @param Vehicules $vehicule Véhicule à supprimer
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la liste des véhicules
     */
    #[Route('/{id}', name: 'app_vehicules_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Vehicules $vehicule, EntityManagerInterface $entityManager): Response
    {
        // Validation du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->request->get('_token'))) {
            // Suppression des images associées du système de fichiers
            foreach ($vehicule->getPictures() as $picture) {
                try {
                    $this->pictureUploadService->deletePicture($picture);
                } catch (\Exception $e) {
                    $this->logger->error('Erreur suppression image', ['error' => $e->getMessage()]);
                }
            }
            
            // Suppression du véhicule de la base de données
            $entityManager->remove($vehicule);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le véhicule a été supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_vehicules_index');
    }

    /**
     * Démarrage du processus d'achat d'un véhicule
     * 
     * Cette méthode gère :
     * - La vérification de l'authentification de l'utilisateur
     * - La vérification de la disponibilité du véhicule
     * - La redirection vers le formulaire de commande
     * 
     * @param Vehicules $vehicule Véhicule à acheter
     * @return Response Redirection vers le formulaire de commande ou page de connexion
     */
    #[Route('/{id}/buy', name: 'app_vehicules_buy', methods: ['GET'])]
    public function buy(Vehicules $vehicule): Response
    {
        $user = $this->getUser();
        
        // Vérification de l'authentification
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un véhicule.');
            return $this->redirectToRoute('app_login');
        }

        // Vérification de la disponibilité du véhicule
        if ($vehicule->getStatus() !== 'Disponible') {
            $this->addFlash('error', 'Ce véhicule n\'est plus disponible à la vente.');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        // Redirection vers le formulaire de commande avec l'ID du véhicule
        return $this->redirectToRoute('app_orders_new_with_vehicule', ['vehicule_id' => $vehicule->getId()]);
    }

    /**
     * Traitement du succès d'un achat (callback Stripe)
     * 
     * Cette méthode gère :
     * - La récupération des informations de session Stripe
     * - La vérification du statut de paiement
     * - La mise à jour du statut du véhicule et de la commande
     * - La gestion des erreurs de traitement
     * 
     * @param Request $request Requête HTTP contenant les paramètres de session
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Page de succès ou redirection en cas d'erreur
     */
    #[Route('/purchase/success', name: 'app_vehicules_purchase_success')]
    public function purchaseSuccess(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sessionId = $request->query->get('session_id');
        $vehiculeId = $request->query->get('vehicule_id');

        try {
            if ($sessionId) {
                // Récupération des détails de la session Stripe
                $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
                
                // Vérification du statut de paiement
                if ($session->payment_status === 'paid') {
                    // Mise à jour du véhicule et de la commande
                    $vehicule = $entityManager->getRepository(Vehicules::class)->find($vehiculeId);
                    $orderId = $session->metadata->order_id ?? null;
                    
                    if ($vehicule && $orderId) {
                        $order = $entityManager->getRepository(Orders::class)->find($orderId);
                        
                        if ($order) {
                            // Mise à jour des statuts
                            $order->setOrderStatus('paid');
                            $vehicule->setStatus('Vendu');
                            
                            $entityManager->flush();
                            
                            $this->addFlash('success', 'Félicitations ! Votre véhicule a été acheté avec succès !');
                        }
                    }
                }
            }

            return $this->render('vehicule/purchase_success.html.twig', [
                'vehicule_id' => $vehiculeId,
            ]);

        } catch (\Exception $e) {
            // Logging de l'erreur pour le debugging
            $this->logger->error('Erreur lors du traitement du succès d\'achat', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'vehicule_id' => $vehiculeId,
            ]);

            $this->addFlash('error', 'Erreur lors du traitement de l\'achat. Contactez le support.');
            return $this->redirectToRoute('app_vehicules_index');
        }
    }

    /**
     * Traitement de l'annulation d'un achat (callback Stripe)
     * 
     * Cette méthode gère :
     * - L'affichage d'un message d'annulation
     * - La redirection vers la liste des véhicules
     * 
     * @param Request $request Requête HTTP
     * @return Response Redirection vers la liste des véhicules
     */
    #[Route('/purchase/cancel', name: 'app_vehicules_purchase_cancel')]
    public function purchaseCancel(Request $request): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_vehicules_index');
    }
} 