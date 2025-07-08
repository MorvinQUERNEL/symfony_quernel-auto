<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Users;
use App\Entity\Preference;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Service de gestion des emails de l'application
 * 
 * Ce service gère :
 * - L'envoi d'emails avec templates Twig
 * - Les emails de bienvenue pour les nouvelles inscriptions
 * - Les confirmations de préférences utilisateur
 * - Les confirmations d'achat avec détails de commande
 * - Les notifications administrateur pour nouveaux achats
 * - La configuration des expéditeurs et destinataires
 * - L'intégration avec Symfony Mailer
 * 
 * Utilise des templates Twig pour un rendu HTML professionnel
 * et une personnalisation complète des emails.
 */
class EmailService
{
    /**
     * Constructeur du service d'email
     * 
     * @param MailerInterface $mailer Service d'envoi d'emails de Symfony
     */
    public function __construct(
        private MailerInterface $mailer
    ) {}

    /**
     * Envoie un email générique avec template Twig
     * 
     * Cette méthode permet d'envoyer des emails personnalisés en utilisant
     * des templates Twig pour le rendu HTML. Elle configure automatiquement
     * l'expéditeur et utilise le service Mailer de Symfony.
     * 
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $template Chemin vers le template Twig (ex: 'emails/registration.html.twig')
     * @param array $context Variables à passer au template Twig
     * 
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface En cas d'erreur d'envoi
     */
    public function sendEmail(string $to, string $subject, string $template, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto')) // Expéditeur configuré
            ->to(new Address($to))
            ->subject($subject)
            ->htmlTemplate($template) // Utilise un template Twig pour le rendu HTML
            ->context($context); // Variables disponibles dans le template

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de bienvenue pour l'inscription d'un nouvel utilisateur
     * 
     * Cette méthode envoie automatiquement un email de bienvenue après
     * l'inscription réussie d'un utilisateur. L'email contient les informations
     * de l'utilisateur pour personnalisation.
     * 
     * @param Users $user Utilisateur nouvellement inscrit
     * 
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface En cas d'erreur d'envoi
     */
    public function sendRegistrationEmail(Users $user): void
    {
        $this->sendEmail(
            $user->getEmail(),
            'Bienvenue chez Quernel Auto',
            'emails/registration.html.twig',
            ['user' => $user] // Passe l'utilisateur au template
        );
    }

    /**
     * Envoie un email de confirmation des préférences utilisateur
     * 
     * Cette méthode envoie une confirmation après l'enregistrement
     * des préférences de véhicules d'un utilisateur. L'email contient
     * un résumé des préférences enregistrées.
     * 
     * @param Users $user Utilisateur ayant défini ses préférences
     * @param Preference $preference Préférences enregistrées
     * 
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface En cas d'erreur d'envoi
     */
    public function sendPreferenceConfirmationEmail(Users $user, Preference $preference): void
    {
        $this->sendEmail(
            $user->getEmail(),
            'Vos préférences ont été enregistrées',
            'emails/preference_confirmation.html.twig',
            [
                'user' => $user,
                'preference' => $preference // Passe les préférences au template
            ]
        );
    }

    /**
     * Envoie un email de confirmation d'achat avec détails de la commande
     * 
     * Cette méthode envoie une confirmation détaillée après un achat réussi.
     * L'email inclut les informations de la commande, du véhicule acheté
     * et une image du véhicule si disponible.
     * 
     * @param Orders $order Commande effectuée
     * @param Users $user Utilisateur ayant effectué l'achat
     * 
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface En cas d'erreur d'envoi
     */
    public function sendPurchaseConfirmation(Orders $order, Users $user): void
    {
        // Récupère le premier véhicule de la commande
        $vehicule = $order->getVehicules()->first();
        $vehiculeImage = null;
        
        // Récupère l'image du véhicule si disponible
        if ($vehicule && $vehicule->getPictures()->count() > 0) {
            $vehiculeImage = '/uploads/vehicules/' . $vehicule->getPictures()->first()->getName();
        }
        
        // Création de l'email avec template personnalisé
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto'))
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('Confirmation d\'achat - Commande #' . $order->getId())
            ->htmlTemplate('emails/purchase_confirmation.html.twig')
            ->context([
                'user' => $user,
                'order' => $order,
                'vehiculeImage' => $vehiculeImage, // Image du véhicule pour le template
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de notification à l'administrateur pour un nouvel achat
     * 
     * Cette méthode notifie automatiquement l'administrateur lorsqu'un
     * nouvel achat est effectué. L'email contient les détails de la commande
     * et les informations du client pour suivi.
     * 
     * @param Orders $order Commande nouvellement effectuée
     * @param Users $user Utilisateur ayant effectué l'achat
     * 
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface En cas d'erreur d'envoi
     */
    public function sendAdminNotification(Orders $order, Users $user): void
    {
        // Récupère le premier véhicule de la commande
        $vehicule = $order->getVehicules()->first();
        
        // Création de l'email de notification administrateur
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto'))
            ->to(new Address('admin@quernel-auto.fr', 'Administrateur'))
            ->subject('Nouvel achat - Commande #' . $order->getId())
            ->htmlTemplate('emails/admin_purchase_notification.html.twig')
            ->context([
                'user' => $user,
                'order' => $order,
                'vehicule' => $vehicule, // Informations du véhicule pour l'admin
            ]);

        $this->mailer->send($email);
    }
} 