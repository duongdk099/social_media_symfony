<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/messages')]
class MessageController extends AbstractController
{
    #[Route('/send', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sender = $this->getUser();
        $receiver = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['receiver_username']]);

        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        if ($sender === $receiver) {
            return $this->json(['error' => 'You cannot send a message to yourself'], 400);
        }

        $message = new Message();
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTime());
        $message->setSender($sender);
        $message->setReceiver($receiver);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json(['message' => 'Message sent successfully'], 201);
    }

    #[Route('/received', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getReceivedMessages(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $messages = $entityManager->getRepository(Message::class)->findBy(['receiver' => $user]);

        return $this->json($messages, 200, [], ['groups' => 'message:read']);
    }

    #[Route('/sent', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getSentMessages(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $messages = $entityManager->getRepository(Message::class)->findBy(['sender' => $user]);

        return $this->json($messages, 200, [], ['groups' => 'message:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMessage(Message $message): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $message);

        return $this->json($message, 200, [], ['groups' => 'message:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteMessage(Message $message, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $message);

        $entityManager->remove($message);
        $entityManager->flush();

        return $this->json(['message' => 'Message deleted successfully'], 200);
    }
}
