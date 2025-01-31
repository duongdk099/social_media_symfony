<?php

namespace App\Tests\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Subworld;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostRepositoryTest extends KernelTestCase
{
    public function testPostCanBeSavedAndRetrieved(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail("postowner@example.com");
        $user->setUsername("postowner");
        $user->setPassword("hashedpassword");
        $entityManager->persist($user);

        $subworld = new Subworld();
        $subworld->setName("Tech World");
        $subworld->setDescription("A subworld for tech enthusiasts.");
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user); 
        $entityManager->persist($subworld);

        $post = new Post();
        $post->setTitle("Test Post");
        $post->setContent("This is a test post content.");
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);
        $post->setSubworld($subworld);
        $entityManager->persist($post);
        $entityManager->flush();

        $postRepository = $entityManager->getRepository(Post::class);
        $savedPost = $postRepository->findOneBy(['title' => 'Test Post']);

        $this->assertNotNull($savedPost);
        $this->assertEquals("Test Post", $savedPost->getTitle());
        $this->assertSame($subworld, $savedPost->getSubworld());
    }
}
