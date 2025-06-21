<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Users;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

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