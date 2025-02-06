<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EntityManagerInterface $em): Response
    {
        // Fetch all posts with metadata
        $posts = $em->createQuery(
            'SELECT p.id AS post_id, p.title, p.content, p.createdAt, 
                    u.username AS author, u.id AS user_id, 
                    s.name AS subworld, s.id AS subworld_id,
                    COUNT(c.id) AS comment_count, 
                    COALESCE(SUM(v.value), 0) AS vote_count
             FROM App\Entity\Post p
             JOIN p.user u
             JOIN p.subworld s
             LEFT JOIN p.comments c
             LEFT JOIN p.votes v
             GROUP BY p.id, u.id, s.id
             ORDER BY p.createdAt DESC'
        )->getResult();

        return $this->render('front/home.html.twig', [
            'posts' => array_map(fn($item) => [
                'id' => $item['post_id'],
                'title' => $item['title'],
                'content' => $item['content'],
                'createdAt' => $item['createdAt'],
                'author' => $item['author'],
                'user_id' => $item['user_id'],
                'subworld' => $item['subworld'],
                'subworld_id' => $item['subworld_id'],
                'comment_count' => $item['comment_count'],
                'vote_count' => $item['vote_count']
            ], $posts),
        ]);
    }
}
