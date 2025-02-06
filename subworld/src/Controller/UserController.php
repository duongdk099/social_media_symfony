<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCurrentUser(): JsonResponse
    {
        return $this->json($this->getUser(), 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getUserById(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $user);
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $user);

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }


        if (isset($data['roles'])) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->json(['error' => 'Only admins can change roles'], 403);
            }
            $user->setRoles($data['roles']);
        }

        $entityManager->flush();

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $user);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully'], 200);
    }

    #[Route('/user/{id}', name: 'app_user_show')]
    public function show(EntityManagerInterface $em, string $id): Response
    {
        // Fetch the user
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Fetch user posts
        $query = $em->createQuery(
            'SELECT p.id AS post_id, p.title, p.content, p.createdAt, 
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
        )->setParameter('user', $user)
            ->getResult();

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'posts' => $query
        ]);
    }

    #[Route('/dashboard', name: 'user_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
    }
}
