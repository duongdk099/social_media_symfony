<?php

namespace App\Controller;

use App\Entity\Subworld;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/subworlds')]
class SubworldController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllSubworlds(EntityManagerInterface $entityManager): JsonResponse
    {
        $subworlds = $entityManager->getRepository(Subworld::class)->findAll();
        return $this->json($subworlds, 200, [], ['groups' => 'subworld:read']);
    }

    #[Route('/user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserSubworlds(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $subworlds = $entityManager->getRepository(Subworld::class)->findBy(['owner' => $user]);
        return $this->json($subworlds, 200, [], ['groups' => 'subworld:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getSubworld(Subworld $subworld, SerializerInterface $serializer): JsonResponse
    {
        $jsonSubworld = $serializer->serialize($subworld, 'json', ['groups' => 'subworld:read']);
        return new JsonResponse($jsonSubworld, 200, [], true);
    }

    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createSubworld(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $subworld = new Subworld();
        $subworld->setName($data['name']);
        $subworld->setDescription($data['description']);
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user);

        $entityManager->persist($subworld);
        $entityManager->flush();

        return $this->json($subworld, 201, [], ['groups' => 'subworld:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editSubworld(Subworld $subworld, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $subworld);

        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) {
            $subworld->setName($data['name']);
        }
        if (isset($data['description'])) {
            $subworld->setDescription($data['description']);
        }

        $entityManager->flush();

        return $this->json($subworld, 200, [], ['groups' => 'subworld:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteSubworld(Subworld $subworld, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isGranted('delete', $subworld)) {
            $entityManager->remove($subworld);
            $entityManager->flush();
            return $this->json(['message' => 'Subworld deleted successfully'], 200);
        }

        return $this->json(['error' => 'Unauthorized action'], 403);
    }
}
