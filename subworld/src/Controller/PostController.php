<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Subworld;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/posts')]
class PostController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllPosts(EntityManagerInterface $entityManager): JsonResponse
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->json($posts, 200, [], ['groups' => 'post:read']);
    }

    #[Route('/create', methods: ['POST'])]
    public function createPost(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        $subworld = $entityManager->getRepository(Subworld::class)->find($data['subworld_id']);

        if (!$user || !$subworld) {
            return $this->json(['error' => 'User or Subworld not found'], 404);
        }

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);
        $post->setSubworld($subworld);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json($post, 201, [], ['groups' => 'post:read']);
    }
}
