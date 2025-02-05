<?php

namespace App\Controller;

use App\Entity\Subworld;
use App\Repository\SubworldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subworld')]
final class SubworldController extends AbstractController
{
    #[Route('/{id}', name: 'app_subworld_show', methods: ['GET'])]
    public function show(Subworld $subworld): Response
    {
        return $this->render('subworld/show.html.twig', [
            'subworld' => $subworld,
            'users_count' => count($subworld->getMembers()), // Count members
            'posts' => $subworld->getPosts(), // Get posts in the community
        ]);
    }
}
