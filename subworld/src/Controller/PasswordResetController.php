<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/password')]
class PasswordResetController extends AbstractController
{
    #[Route('/request-reset', methods: ['POST'])]
    public function requestReset(Request $request, EntityManagerInterface $entityManager, EmailService $emailService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email'])) {
            return $this->json(['error' => 'Email is required'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return $this->json(['message' => 'If an account with this email exists, a reset link has been sent.'], 200);
        }

        $resetToken = bin2hex(random_bytes(32));
        $user->setResetPasswordToken($resetToken);
        $user->setResetPasswordExpiresAt((new \DateTime())->modify('+24 hours'));

        $entityManager->flush();

        $resetUrl = "http://localhost:8000/api/password/reset/$resetToken";
        $emailService->sendPasswordResetEmail($user->getEmail(), $resetUrl);

        return $this->json(['message' => 'A password reset link has been sent to your email.'], 200);
    }

    #[Route('/reset/{token}', methods: ['POST'])]
    public function resetPassword(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['password'])) {
            return $this->json(['error' => 'Password is required'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['resetPasswordToken' => $token]);

        if (!$user || !$user->isResetPasswordTokenValid()) {
            return $this->json(['error' => 'Invalid or expired reset token'], 400);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setResetPasswordToken(null);
        $user->setResetPasswordExpiresAt(null);

        $entityManager->flush();

        return $this->json(['message' => 'Your password has been reset successfully.'], 200);
    }
}
