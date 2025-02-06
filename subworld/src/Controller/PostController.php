<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Subworld;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/posts')]
class PostController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function getAllPosts(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $userId = $user?->getId();

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // Fetch posts with comment count and vote count
        $query = $entityManager->createQuery(
            'SELECT p.id, p.title, p.content, p.createdAt, 
                s.name AS subworld_name, s.id AS subworld_id,
                u.username AS user_name, u.id AS user_id,
                COUNT(c.id) AS comment_count, 
                COALESCE(SUM(v.value), 0) AS vote_count
         FROM App\Entity\Post p
         JOIN p.subworld s
         JOIN p.user u
         LEFT JOIN p.comments c
         LEFT JOIN p.votes v
         GROUP BY p.id, s.id, u.id
         ORDER BY p.createdAt DESC'
        )
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $posts = $query->getResult();

        // Fetch user votes separately if a user is logged in
        $userVotes = [];

        if ($userId) {
            $voteQuery = $entityManager->createQuery(
                'SELECT v.post AS postId, v.value AS voteValue
             FROM App\Entity\Vote v
             WHERE v.user = :userId'
            )->setParameter('userId', $userId);

            foreach ($voteQuery->getResult() as $vote) {
                $userVotes[$vote['postId']] = $vote['voteValue'];
            }
        }

        // Attach the user's vote to each post
        foreach ($posts as &$post) {
            $post['user_vote'] = $userVotes[$post['id']] ?? null;
        }

        // Check if more posts are available
        $totalPosts = $entityManager->getRepository(Post::class)->count([]);
        $hasMore = ($offset + $limit) < $totalPosts;

        return $this->json([
            'posts' => $posts,
            'has_more' => $hasMore
        ], 200, [], ['groups' => 'post:read']);
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

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(EntityManagerInterface $em, int $id): Response
    {
        // Fetch the post
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        // Fetch comments for this post
        $query = $em->createQuery(
            'SELECT c.id, c.content, c.createdAt, 
                    u.username AS author, u.id AS user_id
             FROM App\Entity\Comment c
             JOIN c.user u
             WHERE c.post = :post
             ORDER BY c.createdAt ASC'
        )->setParameter('post', $post)
            ->getResult();

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $query
        ]);
    }

    #[Route('/user/{id}', methods: ['GET'])]
    public function getUserPostsById(string $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch user by ID
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $query = $entityManager->createQuery(
            'SELECT p.id, p.title, p.content, p.createdAt, 
                    s.name AS subworld, s.id AS subworld_id,
                    COUNT(c.id) AS comment_count, 
                    COALESCE(SUM(v.value), 0) AS vote_count
             FROM App\Entity\Post p
             JOIN p.subworld s
             LEFT JOIN p.comments c
             LEFT JOIN p.votes v
             WHERE p.user = :user
             GROUP BY p.id, s.id
             ORDER BY p.createdAt DESC'
        )
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $posts = $query->getResult();

        // Check if more posts are available
        $totalUserPosts = $entityManager->createQuery(
            'SELECT COUNT(p.id) FROM App\Entity\Post p WHERE p.user = :user'
        )
            ->setParameter('user', $user)
            ->getSingleScalarResult();

        $hasMore = ($offset + $limit) < $totalUserPosts;

        return $this->json([
            'posts' => $posts,
            'has_more' => $hasMore
        ], 200, [], ['groups' => 'post:read']);
    }
}
