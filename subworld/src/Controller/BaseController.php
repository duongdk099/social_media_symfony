<?php

namespace App\Controller;

use App\Repository\SubworldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BaseController extends AbstractController
{
    public function __construct(private SubworldRepository $subworldRepository)
    {
    }

    public function getGlobalData(): array
    {
        return [
            'subworlds' => $this->subworldRepository->findAll(),
        ];
    }
}
