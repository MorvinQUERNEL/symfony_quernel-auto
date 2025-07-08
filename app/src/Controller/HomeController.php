<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationType;
use App\Repository\VehiculesRepository;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur principal gérant la page d'accueil et la navigation
 * 
 * Ce contrôleur gère :
 * - L'affichage de la page d'accueil avec formulaire d'inscription
 * - L'inscription des nouveaux utilisateurs
 * - L'envoi d'emails de confirmation
 * - L'affichage des véhicules récents
 * - La navigation avec compteur de messages non lus
 */
class HomeController extends AbstractController
{
    /**
     * Page d'accueil principale
     * 
     * Cette méthode gère :
     * - L'affichage du formulaire d'inscription
     * - Le traitement de l'inscription des nouveaux utilisateurs
     * - L'envoi d'email de confirmation
     * - L'affichage des véhicules récents
     * - La gestion des erreurs et messages flash
     * 
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @param VehiculesRepository $vehiculesRepository Repository des véhicules
     * @param LoggerInterface $logger Service de logging
     * @return Response Page d'accueil avec formulaire et véhicules récents
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, VehiculesRepository $vehiculesRepository, LoggerInterface $logger): Response {
        // Création d'une nouvelle instance utilisateur pour le formulaire
        $user = new Users();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        // Traitement du formulaire d'inscription
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Configuration des rôles utilisateur
                    $user->setRoles(['ROLE_USER']); // Rôles Symfony Security
                    $user->setRole('ROLE_USER'); // Rôle dans la colonne personnalisée

                    // Hachage sécurisé du mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                    $user->setPassword($hashedPassword);
                    $user->setRegistrationAt(new \DateTimeImmutable());

                    // Persistance de l'utilisateur en base de données
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // Préparation et envoi de l'email de confirmation
                    $email = (new TemplatedEmail())
                        ->from('noreply@quernel-auto.com')
                        ->to($user->getEmail())
                        ->subject('Bienvenue chez Quernel Auto!')
                        ->htmlTemplate('emails/registration.html.twig')
                        ->context([
                            'user' => $user,
                        ]);

                    try {
                        $mailer->send($email);
                        $this->addFlash('success', 'Votre compte a été créé avec succès! Un email de confirmation a été envoyé à ' . $user->getEmail());
                        $logger->info('Email de confirmation envoyé avec succès à ' . $user->getEmail());
                    } catch (TransportExceptionInterface $e) {
                        // Gestion des erreurs d'envoi d'email
                        $logger->error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
                        $this->addFlash('error', 'Votre compte a été créé mais l\'email de confirmation n\'a pas pu être envoyé. Notre équipe a été notifiée.');
                    }

                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    // Gestion des erreurs générales
                    $logger->error('Erreur lors de la création du compte: ' . $e->getMessage());
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.');
                }
            } else {
                // Formulaire invalide
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier vos informations.');
            }
        }

        // Récupération des véhicules récents pour l'affichage
        $recentVehicules = $vehiculesRepository->findRecentWithPictures(6);

        return $this->render('home/index.html.twig', [
            'registrationForm' => $form->createView(),
            'recentVehicules' => $recentVehicules,
        ]);
    }

    /**
     * Rendu de la barre de navigation
     * 
     * Cette méthode gère :
     * - L'affichage de la navbar sur toutes les pages
     * - Le compteur de messages non lus pour les administrateurs
     * - L'intégration via render_esi dans base.html.twig
     * 
     * @param MessagesRepository $messagesRepository Repository des messages
     * @return Response Fragment de la barre de navigation
     */
    public function navbar(MessagesRepository $messagesRepository): Response
    {
        $unreadMessagesCount = 0;
        
        // Comptage des messages non lus uniquement pour les administrateurs
        if ($this->isGranted('ROLE_ADMIN')) {
            $unreadMessagesCount = $messagesRepository->countUnreadMessagesForAdmin();
        }

        return $this->render('_navbar.html.twig', [
            'unreadMessagesCount' => $unreadMessagesCount,
        ]);
    }
}
