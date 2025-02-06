<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/votes')]
class VoteController extends AbstractController
{
    #[Route('/post/{id}', methods: ['POST', 'GET'])]  // Allow GET request for fetching vote status
    #[IsGranted('ROLE_USER')]
    public function votePost(Post $post, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        // If it's a GET request, return the user's vote status
        if ($request->isMethod('GET')) {
            $existingVote = $entityManager->getRepository(Vote::class)->findOneBy(['user' => $user, 'post' => $post]);
            return $this->json(['vote_value' => $existingVote ? $existingVote->getValue() : 0]);
        }

        // Handle POST request for voting
        $data = json_decode($request->getContent(), true);
        $value = $data['value'] ?? null;

        if (!in_array($value, [-1, 1])) {
            return $this->json(['error' => 'Invalid vote value. Use -1 (downvote) or 1 (upvote).'], 400);
        }

        $existingVote = $entityManager->getRepository(Vote::class)->findOneBy(['user' => $user, 'post' => $post]);

        if ($existingVote) {
            if ($existingVote->getValue() === $value) {
                $entityManager->remove($existingVote);  // Remove vote if clicked again
            } else {
                $existingVote->setValue($value);  // Update vote value if changed
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setValue($value);
            $entityManager->persist($vote);
        }

        $entityManager->flush();

        // Recalculate vote count
        $newVoteCount = $entityManager->getRepository(Vote::class)->count(['post' => $post]);

        return $this->json([
            'message' => 'Vote recorded successfully',
            'new_vote_count' => $newVoteCount,
            'user_vote' => $existingVote ? $existingVote->getValue() : $value
        ], 201);
    }


    #[Route('/comment/{id}', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function voteComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $value = $data['value'] ?? null;

        if (!in_array($value, [-1, 1])) {
            return $this->json(['error' => 'Invalid vote value. Use -1 (downvote) or 1 (upvote).'], 400);
        }

        $existingVote = $entityManager->getRepository(Vote::class)->findOneBy(['user' => $user, 'comment' => $comment]);

        if ($existingVote) {
            if ($existingVote->getValue() === $value) {
                $entityManager->remove($existingVote);
            } else {
                $existingVote->setValue($value);
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setComment($comment);
            $vote->setValue($value);
            $entityManager->persist($vote);
        }

        $entityManager->flush();

        // Calculate new vote count
        $newVoteCount = $entityManager->getRepository(Vote::class)->count(['comment' => $comment]);

        return $this->json([
            'message' => 'Vote recorded successfully',
            'new_vote_count' => $newVoteCount
        ], 201);
    }

    #[Route('/post/{postId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteVoteForPost(int $postId, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $vote = $entityManager->getRepository(Vote::class)->findOneBy([
            'user' => $user,
            'post' => $post
        ]);

        if (!$vote) {
            return $this->json(['error' => 'No vote found for this post'], 404);
        }

        $entityManager->remove($vote);
        $entityManager->flush();

        return $this->json(['message' => 'Vote removed successfully'], 200);
    }

    #[Route('/comment/{commentId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteVoteForComment(int $commentId, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }

        $vote = $entityManager->getRepository(Vote::class)->findOneBy([
            'user' => $user,
            'comment' => $comment
        ]);

        if (!$vote) {
            return $this->json(['error' => 'No vote found for this comment'], 404);
        }

        $entityManager->remove($vote);
        $entityManager->flush();

        return $this->json(['message' => 'Vote removed successfully'], 200);
    }
}
