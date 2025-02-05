<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiController extends AbstractController
{
    #[Route('/api/protected-route', name: 'api_protected_route', methods: ['GET'])]
    public function protectedRoute(Request $request, JWTEncoderInterface $jwtEncoder, UserRepository $userRepository): JsonResponse
    {

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'No valid Token received'], 401);
        }


        $token = str_replace('Bearer ', '', $authHeader);


        try {
            $decodedToken = $jwtEncoder->decode($token);

            if (!$decodedToken) {
                return $this->json(['error' => 'Token decoding failed'], 401);
            }


            if (!isset($decodedToken['email']) || empty($decodedToken['email'])) {
                return $this->json(['error' => 'Email not found in token', 'decoded_token' => $decodedToken], 401);
            }


            $user = $userRepository->findOneBy(['email' => $decodedToken['email']]);

            if (!$user) {
                return $this->json(['error' => 'User not found'], 401);
            }

            return $this->json([
                'message' => 'Access granted',
                'user' => [
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Exception: ' . $e->getMessage()], 401);
        }
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function getAuthenticatedUser(Request $request, JWTEncoderInterface $jwtEncoder, UserProviderInterface $userProvider): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'No valid Token received'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decodedToken = $jwtEncoder->decode($token);

            if (!$decodedToken || !isset($decodedToken['email'])) {
                return $this->json(['error' => 'Invalid token'], 401);
            }

            $user = $userProvider->loadUserByIdentifier($decodedToken['email']);

            if (!$user) {
                return $this->json(['error' => 'User not found'], 401);
            }

            return $this->json([
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Exception: ' . $e->getMessage()], 401);
        }
    }


}
