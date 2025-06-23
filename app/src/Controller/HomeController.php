<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationType;
use App\Repository\VehiculesRepository;
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

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, VehiculesRepository $vehiculesRepository, LoggerInterface $logger): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Définir les rôles pour Symfony Security
                    $user->setRoles(['ROLE_USER']);
                    // Définir le rôle dans la colonne role
                    $user->setRole('ROLE_USER');

                    $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                    $user->setPassword($hashedPassword);
                    $user->setRegistrationAt(new \DateTimeImmutable());

                    $entityManager->persist($user);
                    $entityManager->flush();

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
                        $logger->error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
                        $this->addFlash('error', 'Votre compte a été créé mais l\'email de confirmation n\'a pas pu être envoyé. Notre équipe a été notifiée.');
                    }

                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    $logger->error('Erreur lors de la création du compte: ' . $e->getMessage());
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.');
                }
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier vos informations.');
            }
        }

        $recentVehicules = $vehiculesRepository->findRecentWithPictures(6);

        return $this->render('home/index.html.twig', [
            'registrationForm' => $form->createView(),
            'recentVehicules' => $recentVehicules,
        ]);
    }
}
