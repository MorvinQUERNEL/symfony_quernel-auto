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

#[Route('/vehicules')]
class VehiculeController extends AbstractController
{
    private StripeClient $stripeClient;
    private string $stripePublicKey;
    private LoggerInterface $logger;
    private PictureUploadService $pictureUploadService;

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
                ->findAllWithPictures();
        }

        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_vehicules_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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
                    try {
                        $this->pictureUploadService->uploadPicture($imageFile, $picture, $vehicule);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                        $this->logger->error('Erreur upload image', ['error' => $e->getMessage()]);
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
    public function edit(Request $request, Vehicules $vehicule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload des nouvelles images
            $picturesData = $form->get('pictures')->getData();
            
            foreach ($picturesData as $picture) {
                $imageFile = $form->get('pictures')->get($picturesData->indexOf($picture))->get('imageFile')->getData();
                
                if ($imageFile && $imageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    try {
                        // Si l'image avait déjà un nom, supprimer l'ancienne
                        if ($picture->getName()) {
                            $this->pictureUploadService->deletePicture($picture);
                        }
                        $this->pictureUploadService->uploadPicture($imageFile, $picture, $vehicule);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                        $this->logger->error('Erreur upload image', ['error' => $e->getMessage()]);
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
            // Supprimer les images du système de fichiers
            foreach ($vehicule->getPictures() as $picture) {
                try {
                    $this->pictureUploadService->deletePicture($picture);
                } catch (\Exception $e) {
                    $this->logger->error('Erreur suppression image', ['error' => $e->getMessage()]);
                }
            }
            
            $entityManager->remove($vehicule);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le véhicule a été supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_vehicules_index');
    }

    #[Route('/{id}/buy', name: 'app_vehicules_buy', methods: ['GET'])]
    public function buy(Vehicules $vehicule): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter un véhicule.');
            return $this->redirectToRoute('app_login');
        }

        if ($vehicule->getStatus() !== 'Disponible') {
            $this->addFlash('error', 'Ce véhicule n\'est plus disponible à la vente.');
            return $this->redirectToRoute('app_vehicules_show', ['id' => $vehicule->getId()]);
        }

        // Rediriger vers le formulaire de commande avec l'ID du véhicule
        return $this->redirectToRoute('app_orders_new_with_vehicule', ['vehicule_id' => $vehicule->getId()]);
    }

    #[Route('/purchase/success', name: 'app_vehicules_purchase_success')]
    public function purchaseSuccess(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sessionId = $request->query->get('session_id');
        $vehiculeId = $request->query->get('vehicule_id');

        try {
            if ($sessionId) {
                // Récupérer les détails de la session Stripe
                $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
                
                if ($session->payment_status === 'paid') {
                    // Mettre à jour le véhicule et la commande
                    $vehicule = $entityManager->getRepository(Vehicules::class)->find($vehiculeId);
                    $orderId = $session->metadata->order_id ?? null;
                    
                    if ($vehicule && $orderId) {
                        $order = $entityManager->getRepository(Orders::class)->find($orderId);
                        
                        if ($order) {
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
            $this->logger->error('Erreur lors du traitement du succès d\'achat', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'vehicule_id' => $vehiculeId,
            ]);

            $this->addFlash('error', 'Erreur lors du traitement de l\'achat. Contactez le support.');
            return $this->redirectToRoute('app_vehicules_index');
        }
    }

    #[Route('/purchase/cancel', name: 'app_vehicules_purchase_cancel')]
    public function purchaseCancel(Request $request): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_vehicules_index');
    }
} 