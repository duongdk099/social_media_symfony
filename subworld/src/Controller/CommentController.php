<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllComments(EntityManagerInterface $entityManager): JsonResponse
    {
        $comments = $entityManager->getRepository(Comment::class)->findBy([], ['createdAt' => 'DESC']); // Sorted by date DESC
        return $this->json($comments, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getComment(Comment $comment, SerializerInterface $serializer): JsonResponse
    {
        $jsonComment = $serializer->serialize($comment, 'json', ['groups' => 'comment:read']);
        return new JsonResponse($jsonComment, 200, [], true);
    }


    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createComment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $post = $entityManager->getRepository(Post::class)->find($data['post_id']);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setUser($user);
        $comment->setPost($post);
        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json($comment, 201, [], ['groups' => 'comment:read']);
    }


    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateComment(Comment $comment, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $comment);

        $data = json_decode($request->getContent(), true);
        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $entityManager->flush();

        return $this->json($comment, 200, [], ['groups' => 'comment:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $comment);

        try {
            $entityManager->remove($comment);
            $entityManager->flush();
            return $this->json(['message' => 'Comment deleted successfully'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete comment'], 500);
        }
    }
}
