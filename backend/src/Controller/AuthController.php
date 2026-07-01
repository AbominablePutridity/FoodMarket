<?php

/**
 * FoodMarket - Controller: AuthController
 * 
 * Регистрация и аутентификация пользователей.
 * Регистрация: создаёт нового пользователя в БД.
 * Логин: происходит через lexik/jwt-authentication-bundle (см. routes.yaml).
 * 
 * После регистрации пользователь получает JWT токен для доступа к API.
 * Токен нужно передавать в заголовке: Authorization: Bearer <token>
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Регистрация нового пользователя
     */
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Регистрация нового пользователя',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', example: 'ROLE_USER', description: 'ROLE_USER или ROLE_ADMIN'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Пользователь создан, возвращает JWT токен'),
            new OA\Response(response: 400, description: 'Ошибка валидации'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // проверяем обязательные поля
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email и пароль обязательны'], 400);
        }

        // проверяем, не занят ли email
        if ($this->userRepository->findByEmail($data['email'])) {
            return $this->json(['error' => 'Пользователь с таким email уже существует'], 400);
        }

        // создаём пользователя
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $data['password'])
        );

        // устанавливаем роль (по умолчанию ROLE_USER)
        $role = $data['role'] ?? 'ROLE_USER';
        if (!in_array($role, ['ROLE_USER', 'ROLE_ADMIN'])) {
            $role = 'ROLE_USER';
        }
        $user->setRoles([$role]);

        // валидируем данные
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // генерируем JWT токен
        $token = $this->jwtManager->create($user);

        return $this->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ], 201);
    }
}
