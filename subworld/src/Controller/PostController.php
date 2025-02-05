<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Subworld;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/posts')]
class PostController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllPosts(EntityManagerInterface $entityManager): JsonResponse
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->json($posts, 200, [], ['groups' => 'post:read']);
    }

    #[Route('/user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserPosts(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $posts = $entityManager->getRepository(Post::class)->findBy(['user' => $user]);
        return $this->json($posts, 200, [], ['groups' => 'post:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getPost(Post $post, SerializerInterface $serializer): JsonResponse
    {
        $jsonPost = $serializer->serialize($post, 'json', ['groups' => 'post:read']);
        return new JsonResponse($jsonPost, 200, [], true);
    }

    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createPost(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $subworld = $entityManager->getRepository(Subworld::class)->find($data['subworld_id']);

        if (!$subworld) {
            return $this->json(['error' => 'Subworld not found'], 404);
        }

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setCreatedAt(new \DateTime());
        $post->setUpdatedAt(new \DateTime());
        $post->setUser($user);
        $post->setSubworld($subworld);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json($post, 201, [], ['groups' => 'post:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function editPost(Post $post, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $post);

        $data = json_decode($request->getContent(), true);
        
        $updated = false;

        if (isset($data['title']) && !empty($data['title'])) {
            $post->setTitle($data['title']);
            $updated = true;
        }
        if (isset($data['content']) && !empty($data['content'])) {
            $post->setContent($data['content']);
            $updated = true;
        }

        if (!$updated) {
            return $this->json(['error' => 'No valid fields to update'], 400);
        }

        $post->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        return $this->json($post, 200, [], ['groups' => 'post:read']);
    }


    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deletePost(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isGranted('delete', $post)) {
            $entityManager->remove($post);
            $entityManager->flush();
            return $this->json(['message' => 'Post deleted successfully'], 200);
        }

        return $this->json(['error' => 'Unauthorized action'], 403);
    }
}
