<?php

namespace App\Controller\Api;

use App\Entity\Preference;
use App\Entity\Users;
use App\Repository\PreferenceRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/preferences')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class PreferenceApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PreferenceRepository $preferenceRepository,
    ) {
    }

    #[Route('', name: 'api_preferences_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $preferences = $this->preferenceRepository->findBy(['users' => $user]);

        $data = array_map(function ($preference) {
            return [
                'id' => $preference->getId(),
                'brand' => $preference->getPreferenceBrand(),
                'model' => $preference->getPreferenceModel(),
                'minYear' => $preference->getMinYear(),
                'maxPrice' => $preference->getMaxPrice(),
                'fuelType' => $preference->getPreferenceFuelType(),
            ];
        }, $preferences);

        return $this->json($data);
    }

    #[Route('', name: 'api_preferences_create', methods: ['POST'])]
    public function create(Request $request, EmailService $emailService): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        $preference = new Preference();
        $preference->setUsers($user);

        if (isset($data['brand'])) $preference->setPreferenceBrand($data['brand']);
        if (isset($data['model'])) $preference->setPreferenceModel($data['model']);
        if (isset($data['minYear'])) $preference->setMinYear($data['minYear']);
        if (isset($data['maxPrice'])) $preference->setMaxPrice((int) $data['maxPrice']);
        if (isset($data['fuelType'])) $preference->setPreferenceFuelType($data['fuelType']);

        $this->entityManager->persist($preference);
        $this->entityManager->flush();

        // Envoyer un email de confirmation
        try {
            $emailService->sendPreferenceConfirmation($preference, $user);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la création
        }

        return $this->json([
            'id' => $preference->getId(),
            'brand' => $preference->getPreferenceBrand(),
            'model' => $preference->getPreferenceModel(),
            'minYear' => $preference->getMinYear(),
            'maxPrice' => $preference->getMaxPrice(),
            'fuelType' => $preference->getPreferenceFuelType(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_preferences_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $preference = $this->preferenceRepository->find($id);

        if (!$preference) {
            return $this->json(['error' => 'Préférence non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est propriétaire
        if ($preference->getUsers() !== $user) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($preference);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
