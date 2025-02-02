<?php

namespace App\Controller;

use App\Entity\Subworld;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/subworlds')]
class SubworldController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllSubworlds(EntityManagerInterface $entityManager): JsonResponse
    {
        $subworlds = $entityManager->getRepository(Subworld::class)->findAll();
        return $this->json($subworlds, 200, [], ['groups' => 'subworld:read']);
    }

    #[Route('/create', methods: ['POST'])]
    public function createSubworld(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['owner_id']);

        if (!$user) {
            return $this->json(['error' => 'Owner not found'], 404);
        }

        $subworld = new Subworld();
        $subworld->setName($data['name']);
        $subworld->setDescription($data['description']);
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user);

        $entityManager->persist($subworld);
        $entityManager->flush();

        return $this->json($subworld, 201, [], ['groups' => 'subworld:read']);
    }
}
