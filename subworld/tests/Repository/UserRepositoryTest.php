<?php

namespace App\Tests\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    public function testUserCanBeSavedAndRetrieved(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail("testuser@example.com");
        $user->setUsername("testuser");
        $user->setPassword("hashedpassword");

        $entityManager->persist($user);
        $entityManager->flush();

        $userRepository = $entityManager->getRepository(User::class);
        $savedUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        $this->assertNotNull($savedUser);
        $this->assertEquals("testuser", $savedUser->getUsername());
    }
}
