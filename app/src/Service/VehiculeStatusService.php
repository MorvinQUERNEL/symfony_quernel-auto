<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Vehicules;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de gestion des statuts des véhicules
 * 
 * Ce service gère :
 * - Le marquage des véhicules comme vendus après paiement
 * - Le marquage des véhicules comme disponibles (annulation)
 * - La vérification de la disponibilité des véhicules
 * - La récupération des statuts actuels
 * - Le logging des changements de statut
 * - La gestion des erreurs lors des mises à jour
 * 
 * Assure la cohérence des statuts des véhicules dans l'application
 * et fournit un suivi détaillé des changements.
 */
class VehiculeStatusService
{
    /**
     * Constructeur du service de gestion des statuts véhicule
     * 
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param LoggerInterface $logger Service de logging
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Marque un véhicule comme vendu suite à un paiement réussi
     * 
     * Cette méthode met à jour le statut d'un véhicule après une vente
     * réussie. Elle gère plusieurs cas :
     * - Utilisation d'un ID véhicule spécifique si fourni
     * - Récupération automatique du véhicule depuis la commande
     * - Vérification que le véhicule n'est pas déjà vendu
     * - Logging détaillé des opérations
     * - Gestion des erreurs avec rollback automatique
     * 
     * @param Orders $order Commande associée à la vente
     * @param int|null $vehiculeId ID du véhicule spécifique (optionnel)
     * 
     * @return bool True si le véhicule a été marqué comme vendu, false sinon
     * 
     * @throws \Doctrine\ORM\ORMException En cas d'erreur de base de données
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

            // Vérification de l'existence du véhicule
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
                return true; // Considéré comme succès car objectif atteint
            }

            // Marquer le véhicule comme vendu
            $vehicule->setStatus('Vendu');
            $this->entityManager->flush(); // Persistance en base de données

            // Logging du succès avec détails
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
            // Logging de l'erreur avec contexte détaillé
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
     * 
     * Cette méthode permet de remettre un véhicule en statut "Disponible",
     * généralement suite à une annulation de commande ou une erreur.
     * Elle effectue :
     * - La mise à jour du statut vers "Disponible"
     * - La persistance en base de données
     * - Le logging de l'opération
     * - La gestion des erreurs
     * 
     * @param Vehicules $vehicule Véhicule à remettre en disponibilité
     * 
     * @return bool True si le véhicule a été marqué comme disponible, false sinon
     * 
     * @throws \Doctrine\ORM\ORMException En cas d'erreur de base de données
     */
    public function markVehiculeAsAvailable(Vehicules $vehicule): bool
    {
        try {
            // Mise à jour du statut vers "Disponible"
            $vehicule->setStatus('Disponible');
            $this->entityManager->flush(); // Persistance en base de données

            // Logging du succès avec détails du véhicule
            $this->logger->info('Véhicule marqué comme disponible', [
                'vehicule_id' => $vehicule->getId(),
                'vehicule_brand' => $vehicule->getBrand(),
                'vehicule_model' => $vehicule->getModel(),
            ]);

            return true;

        } catch (\Exception $e) {
            // Logging de l'erreur avec contexte
            $this->logger->error('Erreur lors du marquage du véhicule comme disponible', [
                'error' => $e->getMessage(),
                'vehicule_id' => $vehicule->getId(),
            ]);
            return false;
        }
    }

    /**
     * Vérifie si un véhicule peut être acheté
     * 
     * Cette méthode vérifie la disponibilité d'un véhicule pour l'achat.
     * Un véhicule peut être acheté uniquement s'il est en statut "Disponible".
     * 
     * @param Vehicules $vehicule Véhicule à vérifier
     * 
     * @return bool True si le véhicule est disponible à l'achat, false sinon
     */
    public function canVehiculeBePurchased(Vehicules $vehicule): bool
    {
        return $vehicule->getStatus() === 'Disponible';
    }

    /**
     * Récupère le statut actuel d'un véhicule
     * 
     * Cette méthode retourne le statut actuel d'un véhicule.
     * Les statuts possibles sont :
     * - "Disponible" : Le véhicule peut être acheté
     * - "Vendu" : Le véhicule a été vendu
     * - "En cours de vente" : Le véhicule est en cours de transaction
     * - "En réparation" : Le véhicule est en maintenance
     * - "Réservé" : Le véhicule est réservé
     * 
     * @param Vehicules $vehicule Véhicule dont on veut connaître le statut
     * 
     * @return string Le statut actuel du véhicule
     */
    public function getVehiculeStatus(Vehicules $vehicule): string
    {
        return $vehicule->getStatus();
    }
} 