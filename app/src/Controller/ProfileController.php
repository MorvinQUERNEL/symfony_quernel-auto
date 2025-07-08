<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Preference;
use App\Repository\PreferenceRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Contrôleur gérant le profil utilisateur
 * 
 * Ce contrôleur gère :
 * - L'affichage du profil utilisateur
 * - La modification des informations personnelles
 * - Le changement de mot de passe
 * - La gestion des préférences véhicule
 * - L'affichage des commandes récentes
 * - La suppression des préférences
 */
#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    /**
     * Page principale du profil utilisateur
     * 
     * Cette méthode gère :
     * - L'affichage des informations personnelles de l'utilisateur
     * - L'affichage des préférences véhicule
     * - L'affichage des 3 commandes les plus récentes
     * - Le rendu de la page de profil
     * 
     * @param PreferenceRepository $preferenceRepository Repository des préférences
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Page de profil utilisateur
     */
    #[Route('/', name: 'app_profile')]
    public function index(PreferenceRepository $preferenceRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        
        // Récupération des commandes de l'utilisateur (les 3 plus récentes pour l'aperçu)
        $orders = $entityManager->getRepository(\App\Entity\Orders::class)
            ->findBy(['users' => $user], ['createdAt' => 'DESC'], 3);
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'preferences' => $preferences,
            'orders' => $orders,
        ]);
    }

    /**
     * Modification du profil utilisateur
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de modification du profil
     * - La validation et traitement des modifications
     * - La mise à jour des informations en base de données
     * - L'affichage des messages de succès
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Formulaire de modification ou redirection après succès
     */
    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Changement de mot de passe
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de changement de mot de passe
     * - La vérification du mot de passe actuel
     * - Le hachage sécurisé du nouveau mot de passe
     * - La validation des données du formulaire
     * - La mise à jour du mot de passe en base de données
     * 
     * @param Request $request Requête HTTP
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Formulaire de changement de mot de passe ou redirection après succès
     */
    #[Route('/change-password', name: 'app_profile_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            
            // Vérification du mot de passe actuel
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->render('profile/change_password.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Hachage et mise à jour du nouveau mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Suppression d'une préférence véhicule
     * 
     * Cette méthode gère :
     * - La vérification des autorisations (propriétaire de la préférence)
     * - La validation du token CSRF
     * - La suppression de la préférence de la base de données
     * - L'affichage des messages de succès
     * 
     * @param Preference $preference Préférence à supprimer
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param Request $request Requête HTTP
     * @return RedirectResponse Redirection vers le profil
     */
    #[Route('/profile/preference/delete/{id}', name: 'app_profile_preference_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deletePreference(Preference $preference, EntityManagerInterface $em, Request $request): RedirectResponse
    {
        // Vérification que l'utilisateur est propriétaire de la préférence
        if ($preference->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        // Validation du token CSRF
        if ($this->isCsrfTokenValid('delete_preference_' . $preference->getId(), $request->request->get('_token'))) {
            $em->remove($preference);
            $em->flush();
            $this->addFlash('success', 'Préférence supprimée avec succès.');
        }
        return $this->redirectToRoute('app_profile');
    }
} 