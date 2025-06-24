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
                // Email principal vers contact@quernel-auto.fr
                $mainEmail = (new Email())
                    ->from(new Address('noreply@quernel-auto.fr', 'Site Web Quernel Auto'))
                    ->to(new Address('contact@quernel-auto.fr', 'Quernel Auto'))
                    ->subject('Nouveau message de contact via le site web')
                    ->html($this->renderView('emails/contact_notification.html.twig', [
                        'email' => $data['email'],
                        'message' => $data['message'],
                        'date' => new \DateTime()
                    ]));

                $mailer->send($mainEmail);

                // Email de confirmation vers l'utilisateur
                $confirmationEmail = (new Email())
                    ->from(new Address('contact@quernel-auto.fr', 'Quernel Auto'))
                    ->to(new Address($data['email']))
                    ->subject('Confirmation de votre message à Quernel Auto')
                    ->html($this->renderView('emails/contact_confirmation.html.twig', [
                        'email' => $data['email'],
                        'message' => $data['message'],
                        'date' => new \DateTime()
                    ]));

                $mailer->send($confirmationEmail);

                $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
                
                // Redirection pour éviter la resoumission du formulaire
                return $this->redirectToRoute('app_contact');

            } catch (\Exception $e) {
                $logger->error('Erreur lors de l\'envoi du message de contact', [
                    'error' => $e->getMessage(),
                    'email' => $data['email']
                ]);
                
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.');
            }
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 