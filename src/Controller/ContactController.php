<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            try {
                $user_email = $form->get('email')->getData();
                $user_message = $form->get('message')->getData();

                // Email principal vers contact@quernelauto.fr
                $email_to_admin = (new Email())
                    ->from(new Address($user_email))
                    ->to(new Address('contact@quernelauto.fr', 'Quernel Auto'))
                    ->subject('Nouveau message de contact de ' . $user_email)
                    ->text($user_message);

                $mailer->send($email_to_admin);

                // Email de confirmation à l'utilisateur
                $email_to_user = (new Email())
                    ->from(new Address('contact@quernelauto.fr', 'Quernel Auto'))
                    ->to(new Address($user_email))
                    ->subject('Confirmation de votre message')
                    ->htmlTemplate('emails/contact_confirmation.html.twig')
                    ->html($this->renderView('emails/contact_confirmation.html.twig', [
                        'email' => $user_email,
                        'message' => $user_message,
                        'date' => new \DateTime()
                    ]));

                $mailer->send($email_to_user);

                $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
                
                // Redirection pour éviter la resoumission du formulaire
                return $this->redirectToRoute('app_contact');

            } catch (\Exception $e) {
                $logger->error('Erreur lors de l\'envoi du message de contact', [
                    'error' => $e->getMessage(),
                    'email' => $user_email
                ]);
                
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.');
            }
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 