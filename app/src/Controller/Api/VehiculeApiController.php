<?php

namespace App\Controller\Api;

use App\Entity\Pictures;
use App\Entity\Vehicules;
use App\Repository\VehiculesRepository;
use App\Service\PictureUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/vehicules')]
class VehiculeApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VehiculesRepository $vehiculesRepository,
    ) {
    }

    #[Route('', name: 'api_vehicules_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $brand = $request->query->get('brand');
        $fuelType = $request->query->get('fuelType');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 12)));

        $queryBuilder = $this->vehiculesRepository->createQueryBuilder('v')
            ->leftJoin('v.pictures', 'p')
            ->addSelect('p');

        // Recherche par marque ou modèle
        if ($search) {
            $queryBuilder
                ->andWhere('v.brand LIKE :search OR v.model LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par marque
        if ($brand) {
            $queryBuilder
                ->andWhere('v.brand = :brand')
                ->setParameter('brand', $brand);
        }

        // Filtre par type de carburant
        if ($fuelType) {
            $queryBuilder
                ->andWhere('v.fuelType = :fuelType')
                ->setParameter('fuelType', $fuelType);
        }

        // Filtre par prix minimum
        if ($minPrice) {
            $queryBuilder
                ->andWhere('v.salePrice >= :minPrice')
                ->setParameter('minPrice', (int) $minPrice);
        }

        // Filtre par prix maximum
        if ($maxPrice) {
            $queryBuilder
                ->andWhere('v.salePrice <= :maxPrice')
                ->setParameter('maxPrice', (int) $maxPrice);
        }

        // Filtre par statut
        if ($status) {
            $queryBuilder
                ->andWhere('v.status = :status')
                ->setParameter('status', $status);
        }

        // Compter le total
        $countQuery = clone $queryBuilder;
        $total = count($countQuery->getQuery()->getResult());

        // Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('v.id', 'DESC');

        $vehicules = $queryBuilder->getQuery()->getResult();

        return $this->json([
            'data' => $vehicules,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ]
        ], Response::HTTP_OK, [], ['groups' => ['vehicule:list']]);
    }

    #[Route('/{id}', name: 'api_vehicules_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $vehicule = $this->vehiculesRepository->find($id);

        if (!$vehicule) {
            return $this->json(['error' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            $vehicule,
            Response::HTTP_OK,
            [],
            ['groups' => ['vehicule:read']]
        );
    }

    #[Route('', name: 'api_vehicules_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        $requiredFields = ['brand', 'model', 'year', 'mileage', 'fuelType', 'trasmission', 'color', 'doorCount', 'salePrice'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        $vehicule = new Vehicules();
        $vehicule->setBrand($data['brand']);
        $vehicule->setModel($data['model']);
        $vehicule->setYear(new \DateTime($data['year']));
        $vehicule->setMileage((int) $data['mileage']);
        $vehicule->setFuelType($data['fuelType']);
        $vehicule->setTrasmission($data['trasmission']);
        $vehicule->setColor($data['color']);
        $vehicule->setDoorCount((int) $data['doorCount']);
        $vehicule->setSalePrice((int) $data['salePrice']);
        $vehicule->setStatus($data['status'] ?? 'Disponible');
        $vehicule->setDescription($data['description'] ?? null);

        $this->entityManager->persist($vehicule);
        $this->entityManager->flush();

        return $this->json(
            $vehicule,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['vehicule:read']]
        );
    }

    #[Route('/{id}', name: 'api_vehicules_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, Request $request): JsonResponse
    {
        $vehicule = $this->vehiculesRepository->find($id);

        if (!$vehicule) {
            return $this->json(['error' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['brand'])) $vehicule->setBrand($data['brand']);
        if (isset($data['model'])) $vehicule->setModel($data['model']);
        if (isset($data['year'])) $vehicule->setYear(new \DateTime($data['year']));
        if (isset($data['mileage'])) $vehicule->setMileage((int) $data['mileage']);
        if (isset($data['fuelType'])) $vehicule->setFuelType($data['fuelType']);
        if (isset($data['trasmission'])) $vehicule->setTrasmission($data['trasmission']);
        if (isset($data['color'])) $vehicule->setColor($data['color']);
        if (isset($data['doorCount'])) $vehicule->setDoorCount((int) $data['doorCount']);
        if (isset($data['salePrice'])) $vehicule->setSalePrice((int) $data['salePrice']);
        if (isset($data['status'])) $vehicule->setStatus($data['status']);
        if (array_key_exists('description', $data)) $vehicule->setDescription($data['description']);

        $this->entityManager->flush();

        return $this->json(
            $vehicule,
            Response::HTTP_OK,
            [],
            ['groups' => ['vehicule:read']]
        );
    }

    #[Route('/{id}', name: 'api_vehicules_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $vehicule = $this->vehiculesRepository->find($id);

        if (!$vehicule) {
            return $this->json(['error' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si le véhicule est lié à une commande
        if ($vehicule->getOrders() !== null) {
            return $this->json(
                ['error' => 'Ce véhicule est lié à une commande et ne peut pas être supprimé'],
                Response::HTTP_CONFLICT
            );
        }

        $this->entityManager->remove($vehicule);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/pictures', name: 'api_vehicules_upload_pictures', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadPictures(int $id, Request $request, PictureUploadService $uploadService): JsonResponse
    {
        $vehicule = $this->vehiculesRepository->find($id);

        if (!$vehicule) {
            return $this->json(['error' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $files = $request->files->get('pictures');

        if (!$files) {
            return $this->json(['error' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
        }

        // S'assurer que c'est un tableau
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploadedPictures = [];

        foreach ($files as $file) {
            try {
                $filename = $uploadService->upload($file);

                $picture = new Pictures();
                $picture->setName($filename);
                $picture->setVehicules($vehicule);

                $this->entityManager->persist($picture);
                $uploadedPictures[] = $picture;
            } catch (\Exception $e) {
                return $this->json(
                    ['error' => 'Erreur lors de l\'upload: ' . $e->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $this->entityManager->flush();

        return $this->json(
            $uploadedPictures,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['picture:read']]
        );
    }

    #[Route('/brands', name: 'api_vehicules_brands', methods: ['GET'])]
    public function getBrands(): JsonResponse
    {
        $brands = $this->vehiculesRepository->createQueryBuilder('v')
            ->select('DISTINCT v.brand')
            ->orderBy('v.brand', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->json($brands);
    }

    #[Route('/fuel-types', name: 'api_vehicules_fuel_types', methods: ['GET'])]
    public function getFuelTypes(): JsonResponse
    {
        $fuelTypes = $this->vehiculesRepository->createQueryBuilder('v')
            ->select('DISTINCT v.fuelType')
            ->orderBy('v.fuelType', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->json($fuelTypes);
    }
}
