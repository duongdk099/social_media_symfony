<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/messages')]
class MessageController extends AbstractController
{
    #[Route('/send', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sender = $entityManager->getRepository(User::class)->find($data['sender_id']);
        $receiver = $entityManager->getRepository(User::class)->find($data['receiver_id']);

        if (!$sender || !$receiver) {
            return $this->json(['error' => 'Invalid users'], 404);
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
}
