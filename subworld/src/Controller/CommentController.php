<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllComments(EntityManagerInterface $entityManager): JsonResponse
    {
        $comments = $entityManager->getRepository(Comment::class)->findAll();
        return $this->json($comments, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getComment(Comment $comment): JsonResponse
    {
        return $this->json($comment, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/create', methods: ['POST'])]
    public function createComment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        $post = $entityManager->getRepository(Post::class)->find($data['post_id']);

        if (!$user || !$post) {
            return $this->json(['error' => 'User or Post not found'], 404);
        }

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setCreatedAt(new \DateTime());
        $comment->setUser($user);
        $comment->setPost($post);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json($comment, 201, [], ['groups' => 'comment:read']);
    }
}
