<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/reports')]
class ReportController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllReports(EntityManagerInterface $entityManager): JsonResponse
    {
        $reports = $entityManager->getRepository(Report::class)->findAll();
        return $this->json($reports, 200, [], ['groups' => 'report:read']);
    }

    #[Route('/create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createReport(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!isset($data['reason'])) {
            return $this->json(['error' => 'Reason is required'], 400);
        }

        if (!isset($data['post_id']) && !isset($data['comment_id'])) {
            return $this->json(['error' => 'Either post_id or comment_id is required'], 400);
        }

        $report = new Report();
        $report->setReason($data['reason']);
        $report->setUser($user);
        $report->setCreatedAt(new \DateTime());

        if (isset($data['post_id'])) {
            $post = $entityManager->getRepository(Post::class)->find($data['post_id']);
            if (!$post) {
                return $this->json(['error' => 'Post not found'], 404);
            }
            $report->setPost($post);
        }

        if (isset($data['comment_id'])) {
            $comment = $entityManager->getRepository(Comment::class)->find($data['comment_id']);
            if (!$comment) {
                return $this->json(['error' => 'Comment not found'], 404);
            }
            $report->setComment($comment);
        }

        $entityManager->persist($report);
        $entityManager->flush();

        return $this->json(['message' => 'Report created successfully'], 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteReport(Report $report, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($report);
        $entityManager->flush();

        return $this->json(['message' => 'Report deleted successfully'], 200);
    }
}
