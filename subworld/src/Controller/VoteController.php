<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/votes')]
class VoteController extends AbstractController
{
    #[Route('/post/{postId}', methods: ['POST'])]
    public function votePost(int $postId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$user || !$post) {
            return $this->json(['error' => 'User or Post not found'], 404);
        }

        $vote = new Vote();
        $vote->setValue($data['value']); // 1 for upvote, -1 for downvote
        $vote->setUser($user);
        $vote->setPost($post);

        $entityManager->persist($vote);
        $entityManager->flush();

        return $this->json(['message' => 'Vote recorded'], 201);
    }

    #[Route('/comment/{commentId}', methods: ['POST'])]
    public function voteComment(int $commentId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$user || !$comment) {
            return $this->json(['error' => 'User or Comment not found'], 404);
        }

        $vote = new Vote();
        $vote->setValue($data['value']);
        $vote->setUser($user);
        $vote->setComment($comment);

        $entityManager->persist($vote);
        $entityManager->flush();

        return $this->json(['message' => 'Vote recorded'], 201);
    }
}
