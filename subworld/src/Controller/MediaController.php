<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/media')]
class MediaController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllMedia(EntityManagerInterface $entityManager): JsonResponse
    {
        $media = $entityManager->getRepository(Media::class)->findAll();
        return $this->json($media, 200, [], ['groups' => 'post:read']);
    }

    #[Route('/post/{postId}', methods: ['GET'])]
    public function getMediaByPost(int $postId, EntityManagerInterface $entityManager): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        return $this->json($post->getMedia(), 200, [], ['groups' => 'post:read']);
    }

    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addMedia(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['url'], $data['type'], $data['post_id'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $post = $entityManager->getRepository(Post::class)->find($data['post_id']);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $media = new Media();
        $media->setUrl($data['url']);
        $media->setType($data['type']);
        $media->setPost($post);

        $entityManager->persist($media);
        $entityManager->flush();

        return $this->json(['message' => 'Media added successfully'], 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteMedia(Media $media, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $media);

        $entityManager->remove($media);
        $entityManager->flush();

        return $this->json(['message' => 'Media deleted successfully'], 200);
    }

}
