<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use OpenApi\Attributes as OA;
// use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/profile', name: 'api_profile', methods: 'GET')]
    #[OA\Get(
        path: '/api/profile',
        summary: 'Retrieve user profile',
        description: 'Fetches the profile details for the authenticated user.',
        tags: ['User Management'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: '1'),
                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function getProfile(): JsonResponse
    {
        $user = $this->getUser(); // Pega o usuário autenticado

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            // Outros campos que você deseja retornar
        ]);
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        path: '/register',
        summary: 'Register a new user',
        description: 'Creates a new user with the provided email and password.',
        tags: ['User Management'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'securepassword')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'User created')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request - Missing password'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal Server Error'
            )
        ],
        security: []
    )]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setRoles(['ROLE_USER']);
        $user->setActive(true); // Assuma que o usuário é ativado no registro
    
        if ($data['password']) { // Certifique-se de que uma senha foi fornecida
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
    
            return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Password is required");
        }
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update a user',
        description: 'Updates the user details based on the provided data.',
        tags: ['User Management'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['email', 'password'], // Adjust these required fields as necessary
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'updated@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newsecurepassword')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'updated@example.com')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data provided'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal Server Error'
            )
        ],
        security: [['bearerAuth' => []]]
    )]    
    public function updateUser(int $id, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $serializer->deserialize($request->getContent(), User::class, 'json', ['object_to_populate' => $user]);
            $entityManager->flush();
            return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }
    }
}
