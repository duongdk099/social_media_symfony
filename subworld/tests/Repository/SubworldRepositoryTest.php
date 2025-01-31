<?php

namespace App\Tests\Repository;

use App\Entity\Subworld;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubworldRepositoryTest extends KernelTestCase
{
    public function testSubworldCanBeSavedAndRetrieved(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail("subworldowner@example.com");
        $user->setUsername("subworldowner");
        $user->setPassword("hashedpassword");
        $entityManager->persist($user);

        $subworld = new Subworld();
        $subworld->setName("Tech Community");
        $subworld->setDescription("A subworld for tech enthusiasts.");
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user);
        $entityManager->persist($subworld);
        $entityManager->flush();

        $subworldRepository = $entityManager->getRepository(Subworld::class);
        $savedSubworld = $subworldRepository->findOneBy(['name' => 'Tech Community']);
        $this->assertNotNull($savedSubworld);
        $this->assertEquals("Tech Community", $savedSubworld->getName());
    }
}
