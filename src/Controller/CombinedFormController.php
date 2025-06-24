<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Preference;
use App\Form\CombinedFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CombinedFormController extends AbstractController
{
    #[Route('/inscription-preference', name: 'app_combined_form')]
    public function combinedForm(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        // Créer les entités vides
        $user = new Users();
        $preference = new Preference();

        // Créer le formulaire combiné
        $form = $this->createForm(CombinedFormType::class, $user);
        
        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Encoder le mot de passe
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword($user, $plainPassword)
                    );
                }

                // Définir les rôles par défaut
                $user->setRoles(['ROLE_USER']);
                
                // Sauvegarder l'utilisateur d'abord
                $entityManager->persist($user);
                $entityManager->flush();

                // Récupérer les données de préférence du formulaire
                $preferenceData = [
                    'brand' => $form->get('prefereneceBrand')->getData(),
                    'model' => $form->get('preferenceModel')->getData(),
                    'minYear' => $form->get('minYear')->getData(),
                    'maxPrice' => $form->get('maxPrice')->getData(),
                    'fuelType' => $form->get('preferenceFuelType')->getData()
                ];

                // Créer la préférence seulement si des données sont présentes
                $hasPreferences = false;
                if ($preferenceData['brand'] || $preferenceData['model'] || 
                    $preferenceData['minYear'] || $preferenceData['maxPrice'] || 
                    $preferenceData['fuelType']) {
                    
                    $preference->setPrefereneceBrand($preferenceData['brand']);
                    $preference->setPreferenceModel($preferenceData['model']);
                    $preference->setMinYear($preferenceData['minYear']);
                    $preference->setMaxPrice($preferenceData['maxPrice'] ?: 0);
                    // S'assurer que preferenceFuelType n'est jamais null ou vide
                    $preference->setPreferenceFuelType($preferenceData['fuelType'] ?: 'indifférent');
                    $preference->setUsers($user);
                    
                    $entityManager->persist($preference);
                    $entityManager->flush();
                    $hasPreferences = true;
                }

                // Envoyer un email de confirmation
                try {
                    // Email de bienvenue
                    $emailService->sendRegistrationEmail($user);
                    
                    // Email de confirmation des préférences si des préférences ont été définies
                    if ($hasPreferences) {
                        $emailService->sendPreferenceConfirmationEmail($user, $preference);
                    }
                } catch (\Exception $e) {
                    // Log l'erreur mais ne pas bloquer l'inscription
                    error_log('Erreur lors de l\'envoi d\'email: ' . $e->getMessage());
                    $this->addFlash('warning', 'Inscription réussie mais l\'email de confirmation n\'a pas pu être envoyé.');
                }

                $this->addFlash('success', 'Inscription réussie ! Vos préférences ont été enregistrées.');

                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                // En cas d'erreur, annuler les changements seulement si une transaction est active
                if ($entityManager->getConnection()->isTransactionActive()) {
                    $entityManager->rollback();
                }
                
                // Log l'erreur pour le débogage
                error_log('Erreur lors de l\'inscription: ' . $e->getMessage());
                error_log('Trace: ' . $e->getTraceAsString());
                
                // Message d'erreur spécifique
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                    strpos($e->getMessage(), 'UNIQUE constraint failed') !== false ||
                    strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                    $this->addFlash('error', 'Un utilisateur avec cet email existe déjà. Veuillez utiliser un autre email.');
                } else {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
                }
            }
        }

        return $this->render('combined_form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 