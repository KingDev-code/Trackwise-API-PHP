<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        throw new \LogicException('The JSON authentication handler should handle this response.');
    }

    #[Route('/api/logout', name: 'app_logout', methods: ['GET'])]
    #[OA\Get(
        path: '/api/logout',
        summary: 'Logs out the current user',
        description: 'Logs out the current user and invalidates the session.',
        tags: ['User Management'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 302,
                description: 'Successful logout and redirect'
            ),
            new OA\Response(
                response: 401,
                description: 'User is not authenticated'
            )
        ]
    )]
    public function logout(): JsonResponse
    {
        // Optionally, you can invalidate the token here.
        // This depends on your implementation of token management.
        // For example, you might use a blacklist or change a token's validity in your database.
        // The following code is just an illustrative comment:
        //
        // $token = $this->getUser()->getToken(); // Adjust based on your implementation
        // $this->tokenManager->invalidate($token); // Hypothetical token manager service
        
        return new JsonResponse(['message' => 'Successfully logged out'], 200);
    }
}
