<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserNotifications(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $notifications = $entityManager->getRepository(Notification::class)->findBy(['user' => $user]);

        return $this->json($notifications, 200, [], ['groups' => 'notification:read']);
    }

    #[Route('/{id}/read', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function markAsRead(Notification $notification, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $notification); // Vérifie les droits

        $notification->setIsRead(true);
        $entityManager->flush();

        return $this->json(['message' => 'Notification marked as read'], 200);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteNotification(Notification $notification, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $notification); // Vérifie les droits

        $entityManager->remove($notification);
        $entityManager->flush();

        return $this->json(['message' => 'Notification deleted'], 200);
    }

    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createNotification(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $notification = new Notification();
        $notification->setMessage($data['message']);
        $notification->setCreatedAt(new \DateTime());
        $notification->setIsRead(false);
        $notification->setUser($user);

        $entityManager->persist($notification);
        $entityManager->flush();

        return $this->json($notification, 201, [], ['groups' => 'notification:read']);
    }
}
