<?php

namespace App\EventSubscriber;

use App\Entity\Subworld;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Environment $twig;

    public function __construct(EntityManagerInterface $entityManager, Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        // Fetch the top 3 popular subworlds
        $popularSubworlds = $this->entityManager->createQuery(
            'SELECT s.id, s.name, COUNT(u.id) as member_count
             FROM App\Entity\Subworld s 
             JOIN s.members u 
             GROUP BY s.id 
             ORDER BY member_count DESC'
        )->setMaxResults(3)->getResult();

        // Make it globally available in Twig
        $this->twig->addGlobal('popular_subworlds', $popularSubworlds);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onKernelController',
        ];
    }
}
