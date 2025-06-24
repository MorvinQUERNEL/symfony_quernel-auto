<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Users;
use App\Entity\Preference;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    /**
     * Envoie un email générique avec template
     */
    public function sendEmail(string $to, string $subject, string $template, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto'))
            ->to(new Address($to))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de bienvenue pour l'inscription
     */
    public function sendRegistrationEmail(Users $user): void
    {
        $this->sendEmail(
            $user->getEmail(),
            'Bienvenue chez Quernel Auto',
            'emails/registration.html.twig',
            ['user' => $user]
        );
    }

    /**
     * Envoie un email de confirmation des préférences
     */
    public function sendPreferenceConfirmationEmail(Users $user, Preference $preference): void
    {
        $this->sendEmail(
            $user->getEmail(),
            'Vos préférences ont été enregistrées',
            'emails/preference_confirmation.html.twig',
            [
                'user' => $user,
                'preference' => $preference
            ]
        );
    }

    /**
     * Envoie un email de confirmation d'achat
     */
    public function sendPurchaseConfirmation(Orders $order, Users $user): void
    {
        $vehicule = $order->getVehicules()->first();
        $vehiculeImage = null;
        if ($vehicule && $vehicule->getPictures()->count() > 0) {
            $vehiculeImage = '/uploads/vehicules/' . $vehicule->getPictures()->first()->getName();
        }
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto'))
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('Confirmation d\'achat - Commande #' . $order->getId())
            ->htmlTemplate('emails/purchase_confirmation.html.twig')
            ->context([
                'user' => $user,
                'order' => $order,
                'vehiculeImage' => $vehiculeImage,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de notification à l'administrateur pour un nouvel achat
     */
    public function sendAdminNotification(Orders $order, Users $user): void
    {
        $vehicule = $order->getVehicules()->first();
        
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@quernel-auto.fr', 'Quernel Auto'))
            ->to(new Address('admin@quernel-auto.fr', 'Administrateur'))
            ->subject('Nouvel achat - Commande #' . $order->getId())
            ->htmlTemplate('emails/admin_purchase_notification.html.twig')
            ->context([
                'user' => $user,
                'order' => $order,
                'vehicule' => $vehicule,
            ]);

        $this->mailer->send($email);
    }
} 