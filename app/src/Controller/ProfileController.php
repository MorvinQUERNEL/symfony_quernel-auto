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

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(PreferenceRepository $preferenceRepository): Response
    {
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'preferences' => $preferences,
        ]);
    }

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

    #[Route('/profile/preference/delete/{id}', name: 'app_profile_preference_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deletePreference(Preference $preference, EntityManagerInterface $em, Request $request): RedirectResponse
    {
        if ($preference->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete_preference_' . $preference->getId(), $request->request->get('_token'))) {
            $em->remove($preference);
            $em->flush();
            $this->addFlash('success', 'Préférence supprimée avec succès.');
        }
        return $this->redirectToRoute('app_profile');
    }
} 