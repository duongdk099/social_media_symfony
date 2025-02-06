<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\EmailService;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, EmailService $emailService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['username'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email format'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email already in use'], 400);
        }

        if (strlen($data['password']) < 6) {
            return $this->json(['error' => 'Password must be at least 6 characters long'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);

        $roleUser = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
        if (!$roleUser) {
            return $this->json(['error' => 'ROLE_USER role not found in the database'], 500);
        }
        $user->addRoleEntity($roleUser);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $verificationToken = bin2hex(random_bytes(32));
        $user->setVerificationToken($verificationToken);
        $user->setVerificationTokenExpiresAt((new \DateTime())->modify('+24 hours'));

        $entityManager->persist($user);
        $entityManager->flush();

        $emailService->sendAdminNotification($user->getEmail(), $user->getUsername());

        $verificationUrl = "http://localhost:8000/api/auth/verify-email/$verificationToken";
        $emailService->sendVerificationEmail($user->getEmail(), $verificationUrl);

        return $this->json(['message' => 'User registered successfully. Please check your email to verify your account.'], 201);
    }

    #[Route('/register', name: 'app_register_form', methods: ['GET'])]
    public function registerForm(): Response
    {
        return $this->render('auth/register.html.twig');
    }



    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);


        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Missing email or password'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user->isVerified()) {
            return $this->json(['error' => 'Please verify your email before logging in'], 403);
        }

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        error_log("User found: " . $user->getEmail());

        $token = $jwtManager->createFromPayload($user, [
            'roles' => $user->getRoles(),
            'sub' => $user->getUserIdentifier(),
        ]);

        return $this->json(['token' => $token, 'user_id' => $user->getId()]);
    }

    #[Route('/login', name: 'app_login_form', methods: ['GET'])]
    public function loginForm(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('auth/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(['message' => 'Logout successful'], 200);
    }

    #[Route('/refresh', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function refreshToken(JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedException();
        }

        $newToken = $jwtManager->createFromPayload($user, [
            'sub' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);

        return $this->json(['token' => $newToken]);
    }

    #[Route('/verify-email/{token}', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return $this->json(['error' => 'Invalid token'], 400);
        }

        if ($user->getVerificationTokenExpiresAt() < new \DateTime()) {
            return $this->json(['error' => 'Token expired. Please request a new verification email.'], 400);
        }

        $user->setVerifiedAt(new \DateTime());
        $user->setVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationTokenExpiresAt(null);

        $entityManager->flush();

        return $this->json(['message' => 'Email verified successfully. You can now log in.'], 200);
    }
}
