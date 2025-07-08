<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * Contrôleur gérant la réinitialisation de mot de passe
 * 
 * Ce contrôleur gère :
 * - La demande de réinitialisation de mot de passe
 * - L'envoi d'emails avec tokens de réinitialisation
 * - La validation des tokens de réinitialisation
 * - La réinitialisation effective du mot de passe
 * - La gestion de l'expiration des tokens
 * - La sécurité des tokens de réinitialisation
 */
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    /**
     * Constructeur avec injection du gestionnaire d'entités
     * 
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     */
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Demande de réinitialisation de mot de passe
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire de demande de réinitialisation
     * - La validation de l'email fourni
     * - La génération d'un token sécurisé de réinitialisation
     * - L'envoi d'email avec le lien de réinitialisation
     * - La gestion des erreurs (email inexistant)
     * - Le stockage temporaire du token en session
     * 
     * @param Request $request Requête HTTP
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @return Response Formulaire de demande ou redirection
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Génération d'un token sécurisé de réinitialisation
                $token = bin2hex(random_bytes(32));
                
                // Stockage temporaire du token en session avec expiration
                $request->getSession()->set('reset_token_' . $token, [
                    'user_id' => $user->getId(),
                    'expires_at' => time() + 3600 // Expire dans 1 heure
                ]);

                // Préparation et envoi de l'email de réinitialisation
                $email = (new TemplatedEmail())
                    ->from(new Address('contact@quernelauto.fr', 'Quernel Auto'))
                    ->to($user->getEmail())
                    ->subject('Votre demande de réinitialisation de mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'token' => $token,
                        'expires_at' => new \DateTime('+1 hour')
                    ])
                ;

                $mailer->send($email);
                
                $this->addFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse email.');
                return $this->redirectToRoute('app_check_email');
            } else {
                // Gestion du cas où l'email n'existe pas
                $this->addFlash('error', 'Aucun compte associé à cette adresse email.');
                return $this->redirectToRoute('app_forgot_password_request');
            }
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Page de confirmation d'envoi d'email
     * 
     * Cette méthode gère :
     * - L'affichage de la page de confirmation
     * - L'information à l'utilisateur que l'email a été envoyé
     * 
     * @return Response Page de confirmation d'envoi
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('reset_password/check_email.html.twig');
    }

    /**
     * Réinitialisation effective du mot de passe
     * 
     * Cette méthode gère :
     * - La validation du token de réinitialisation
     * - La vérification de l'expiration du token
     * - L'affichage du formulaire de nouveau mot de passe
     * - Le hachage sécurisé du nouveau mot de passe
     * - La mise à jour du mot de passe en base de données
     * - La suppression du token de session
     * - La redirection vers la page de connexion
     * 
     * @param Request $request Requête HTTP
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     * @param string $token Token de réinitialisation
     * @return Response Formulaire de réinitialisation ou redirection
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token): Response
    {
        // Récupération et validation du token depuis la session
        $tokenData = $request->getSession()->get('reset_token_' . $token);

        // Vérification de l'existence et de l'expiration du token
        if (!$tokenData || $tokenData['expires_at'] < time()) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Récupération et validation de l'utilisateur
        $user = $this->entityManager->getRepository(Users::class)->find($tokenData['user_id']);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage sécurisé du nouveau mot de passe
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // Mise à jour du mot de passe en base de données
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // Nettoyage du token de session
            $request->getSession()->remove('reset_token_' . $token);
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 