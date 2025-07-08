<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Contrôleur gérant l'inscription des nouveaux utilisateurs
 * 
 * Ce contrôleur gère :
 * - L'affichage du formulaire d'inscription
 * - Le traitement de l'inscription avec validation
 * - Le hachage sécurisé des mots de passe
 * - L'envoi d'emails de bienvenue
 * - La gestion des erreurs (email dupliqué, etc.)
 * - La persistance des utilisateurs en base de données
 */
class RegistrationController extends AbstractController
{
    /**
     * Inscription d'un nouvel utilisateur
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire d'inscription
     * - La validation des données du formulaire
     * - Le hachage sécurisé du mot de passe
     * - La persistance de l'utilisateur en base de données
     * - L'envoi d'email de bienvenue
     * - La gestion des erreurs (email dupliqué, échec d'envoi d'email)
     * - La redirection après inscription réussie
     * 
     * @param Request $request Requête HTTP
     * @param UserPasswordHasherInterface $userPasswordHasher Service de hachage des mots de passe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @return Response Formulaire d'inscription ou redirection après succès
     */
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Définition de la date d'inscription
                $user->setRegistrationAt(new \DateTimeImmutable());
                
                // Hachage sécurisé du mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Persistance de l'utilisateur en base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Envoi de l'email de bienvenue
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@quernel-auto.fr', 'Quernel Auto'))
                    ->to($user->getEmail())
                    ->subject('Bienvenue chez Quernel Auto !')
                    ->htmlTemplate('emails/registration.html.twig')
                    ->context([
                        'user' => $user,
                    ]);

                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Votre compte a été créé avec succès. Un email de bienvenue vous a été envoyé.');
                } catch (TransportExceptionInterface $e) {
                    // Gestion de l'échec de l'envoi de l'email
                    $this->addFlash('warning', 'Votre compte a été créé, mais l\'email de bienvenue n\'a pas pu être envoyé.');
                }

                return $this->redirectToRoute('app_home');
                
            } catch (UniqueConstraintViolationException $e) {
                // Gestion des erreurs de contrainte unique (email dupliqué)
                if (strpos($e->getMessage(), 'UNIQ_IDENTIFIER_EMAIL') !== false) {
                    $this->addFlash('error', 'Cette adresse email est déjà utilisée. Veuillez utiliser une autre adresse email ou vous connecter si vous avez déjà un compte.');
                } else {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
                }
                
                // Réinitialisation du formulaire pour permettre une nouvelle tentative
                $user = new Users();
                $form = $this->createForm(RegistrationFormType::class, $user);
            } catch (\Exception $e) {
                // Gestion des erreurs générales
                $this->addFlash('error', 'Une erreur inattendue est survenue. Veuillez réessayer plus tard.');
                
                // Réinitialisation du formulaire pour permettre une nouvelle tentative
                $user = new Users();
                $form = $this->createForm(RegistrationFormType::class, $user);
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
