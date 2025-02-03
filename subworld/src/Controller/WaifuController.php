<?php

namespace App\Controller;

use App\Service\WaifuApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WaifuController extends AbstractController
{
    #[Route('/waifu', name: 'waifu_fetch')]
    public function getWaifu(WaifuApiService $waifuApiService): Response
    {
        $params = [
            'included_tags' => ['raiden-shogun', 'maid'],
            'height' => '>=2000'
        ];

        $data = $waifuApiService->fetchWaifu($params);

        return $this->render('waifu/index.html.twig', [
            'waifus' => $data['images'] ?? [],
        ]);
    }
}
