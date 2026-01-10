<?php

namespace App\Controller\Api;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Cette route est gérée par le firewall json_login dans security.yaml
        // Le JWT est retourné automatiquement par lexik_jwt_authentication
        return $this->json(['message' => 'Cette route est gérée par le firewall JWT'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request, EmailService $emailService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que tous les champs requis sont présents
        $requiredFields = ['email', 'password', 'firstname', 'lastname', 'phoneNumber', 'address', 'city', 'postalCode', 'country'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Vérifier si l'email existe déjà
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Cet email est déjà utilisé'], Response::HTTP_CONFLICT);
        }

        $user = new Users();
        $user->setEmail($data['email']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setPhoneNumber($data['phoneNumber']);
        $user->setAddress($data['address']);
        $user->setCity($data['city']);
        $user->setPostalCode((int) $data['postalCode']);
        $user->setCountry($data['country']);
        $user->setRoles(['ROLE_USER']);

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Envoyer l'email de bienvenue
        try {
            $emailService->sendRegistrationConfirmation($user);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer l'inscription
        }

        return $this->json(
            $user,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Avec JWT stateless, le logout se fait côté client en supprimant le token
        // On peut implémenter une blacklist de tokens si nécessaire
        return $this->json(['message' => 'Déconnexion réussie']);
    }

    #[Route('/password-reset/request', name: 'api_auth_password_reset_request', methods: ['POST'])]
    public function passwordResetRequest(Request $request, UsersRepository $usersRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email'])) {
            return $this->json(['error' => "L'email est requis"], Response::HTTP_BAD_REQUEST);
        }

        $user = $usersRepository->findOneBy(['email' => $data['email']]);

        // Pour des raisons de sécurité, on retourne toujours un succès
        // même si l'email n'existe pas
        if ($user) {
            // TODO: Implémenter l'envoi de l'email de réinitialisation
            // Utiliser SymfonyMailer avec un token de réinitialisation
        }

        return $this->json([
            'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation'
        ]);
    }
}
