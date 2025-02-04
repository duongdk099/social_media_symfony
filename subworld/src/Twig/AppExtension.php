<?php

namespace App\Twig;

use App\Repository\SubworldRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private SubworldRepository $subworldRepository;

    public function __construct(SubworldRepository $subworldRepository)
    {
        $this->subworldRepository = $subworldRepository;
    }

    public function getGlobals(): array
    {
        return [
            'subworlds' => $this->subworldRepository->findAll(),
        ];
    }
}
