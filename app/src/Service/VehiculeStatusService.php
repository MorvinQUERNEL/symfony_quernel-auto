<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Vehicules;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class VehiculeStatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Marque un véhicule comme vendu suite à un paiement réussi
     */
    public function markVehiculeAsSold(Orders $order, ?int $vehiculeId = null): bool
    {
        try {
            $vehicule = null;

            // Si un vehicule_id est fourni, l'utiliser en priorité
            if ($vehiculeId) {
                $vehicule = $this->entityManager->getRepository(Vehicules::class)->find($vehiculeId);
            }

            // Sinon, récupérer le premier véhicule de la commande
            if (!$vehicule) {
                $vehicule = $order->getVehicules()->first();
            }

            if (!$vehicule) {
                $this->logger->warning('Aucun véhicule trouvé pour marquer comme vendu', [
                    'order_id' => $order->getId(),
                    'vehicule_id' => $vehiculeId,
                ]);
                return false;
            }

            // Vérifier que le véhicule n'est pas déjà vendu
            if ($vehicule->getStatus() === 'Vendu') {
                $this->logger->info('Véhicule déjà marqué comme vendu', [
                    'vehicule_id' => $vehicule->getId(),
                    'order_id' => $order->getId(),
                ]);
                return true;
            }

            // Marquer le véhicule comme vendu
            $vehicule->setStatus('Vendu');
            $this->entityManager->flush();

            $this->logger->info('Véhicule marqué comme vendu avec succès', [
                'vehicule_id' => $vehicule->getId(),
                'vehicule_brand' => $vehicule->getBrand(),
                'vehicule_model' => $vehicule->getModel(),
                'vehicule_previous_status' => $vehicule->getStatus(),
                'order_id' => $order->getId(),
                'order_total_price' => $order->getTotalPrice(),
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du marquage du véhicule comme vendu', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
                'vehicule_id' => $vehiculeId,
            ]);
            return false;
        }
    }

    /**
     * Marque un véhicule comme disponible (annulation de vente)
     */
    public function markVehiculeAsAvailable(Vehicules $vehicule): bool
    {
        try {
            $vehicule->setStatus('Disponible');
            $this->entityManager->flush();

            $this->logger->info('Véhicule marqué comme disponible', [
                'vehicule_id' => $vehicule->getId(),
                'vehicule_brand' => $vehicule->getBrand(),
                'vehicule_model' => $vehicule->getModel(),
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du marquage du véhicule comme disponible', [
                'error' => $e->getMessage(),
                'vehicule_id' => $vehicule->getId(),
            ]);
            return false;
        }
    }

    /**
     * Vérifie si un véhicule peut être acheté
     */
    public function canVehiculeBePurchased(Vehicules $vehicule): bool
    {
        return $vehicule->getStatus() === 'Disponible';
    }

    /**
     * Récupère le statut actuel d'un véhicule
     */
    public function getVehiculeStatus(Vehicules $vehicule): string
    {
        return $vehicule->getStatus();
    }
} 