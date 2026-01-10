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
            return $this->json([
                'error' => 'Données invalides',
                'message' => 'Le format des données envoyées est incorrect.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];

        // Validation des champs requis avec messages personnalisés
        if (empty($data['email'])) {
            $errors['email'] = 'L\'adresse email est requise.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'adresse email n\'est pas valide.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Le mot de passe est requis.';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!preg_match('/[A-Z]/', $data['password'])) {
            $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule.';
        } elseif (!preg_match('/[0-9]/', $data['password'])) {
            $errors['password'] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        if (empty($data['firstname'])) {
            $errors['firstname'] = 'Le prénom est requis.';
        } elseif (strlen($data['firstname']) < 2) {
            $errors['firstname'] = 'Le prénom doit contenir au moins 2 caractères.';
        } elseif (strlen($data['firstname']) > 50) {
            $errors['firstname'] = 'Le prénom ne peut pas dépasser 50 caractères.';
        }

        if (empty($data['lastname'])) {
            $errors['lastname'] = 'Le nom est requis.';
        } elseif (strlen($data['lastname']) < 2) {
            $errors['lastname'] = 'Le nom doit contenir au moins 2 caractères.';
        } elseif (strlen($data['lastname']) > 50) {
            $errors['lastname'] = 'Le nom ne peut pas dépasser 50 caractères.';
        }

        // Validation du téléphone si fourni
        if (!empty($data['phone']) && !preg_match('/^[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,9}$/', $data['phone'])) {
            $errors['phone'] = 'Le numéro de téléphone n\'est pas valide.';
        }

        // Si des erreurs de validation, les retourner
        if (!empty($errors)) {
            return $this->json([
                'error' => 'Erreurs de validation',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json([
                'error' => 'Email déjà utilisé',
                'errors' => ['email' => 'Cette adresse email est déjà associée à un compte.']
            ], Response::HTTP_CONFLICT);
        }

        $user = new Users();
        $user->setEmail(trim($data['email']));
        $user->setFirstname(trim($data['firstname']));
        $user->setLastname(trim($data['lastname']));
        $user->setRoles(['ROLE_USER']);

        // Champs optionnels
        if (!empty($data['phone'])) {
            $user->setPhoneNumber(trim($data['phone']));
        }
        if (!empty($data['address'])) {
            $user->setAddress(trim($data['address']));
        }
        if (!empty($data['city'])) {
            $user->setCity(trim($data['city']));
        }
        if (!empty($data['postalCode'])) {
            $user->setPostalCode((int) $data['postalCode']);
        }
        if (!empty($data['country'])) {
            $user->setCountry(trim($data['country']));
        }

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Validation Symfony (contraintes sur l'entité)
        $validationErrors = $this->validator->validate($user);
        if (count($validationErrors) > 0) {
            $errorMessages = [];
            foreach ($validationErrors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'error' => 'Erreurs de validation',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Envoyer l'email de bienvenue
        try {
            $emailService->sendRegistrationEmail($user);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer l'inscription
        }

        return $this->json([
            'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
            ]
        ], Response::HTTP_CREATED);
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
