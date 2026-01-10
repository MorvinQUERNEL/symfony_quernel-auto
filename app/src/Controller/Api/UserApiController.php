<?php

namespace App\Controller\Api;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/me', name: 'api_users_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/me', name: 'api_users_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour des champs modifiables
        if (isset($data['firstname'])) $user->setFirstname($data['firstname']);
        if (isset($data['lastname'])) $user->setLastname($data['lastname']);
        if (isset($data['phoneNumber'])) $user->setPhoneNumber($data['phoneNumber']);
        if (isset($data['address'])) $user->setAddress($data['address']);
        if (isset($data['city'])) $user->setCity($data['city']);
        if (isset($data['postalCode'])) $user->setPostalCode((int) $data['postalCode']);
        if (isset($data['country'])) $user->setCountry($data['country']);

        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/me/password', name: 'api_users_change_password', methods: ['PUT'])]
    public function changePassword(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier les champs requis
        if (empty($data['currentPassword']) || empty($data['newPassword'])) {
            return $this->json(
                ['error' => 'Le mot de passe actuel et le nouveau mot de passe sont requis'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérifier le mot de passe actuel
        if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->json(
                ['error' => 'Le mot de passe actuel est incorrect'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérifier la longueur du nouveau mot de passe
        if (strlen($data['newPassword']) < 8) {
            return $this->json(
                ['error' => 'Le nouveau mot de passe doit contenir au moins 8 caractères'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Hasher et enregistrer le nouveau mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();

        return $this->json(['message' => 'Mot de passe modifié avec succès']);
    }
}
