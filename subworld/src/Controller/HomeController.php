<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\Subworld;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch latest posts (limit 10)
        $posts = $entityManager->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC'], 10);

        // Fetch list of subworlds
        $subworlds = $entityManager->getRepository(Subworld::class)->findAll();

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'subworlds' => $subworlds,
        ]);
    }
}
