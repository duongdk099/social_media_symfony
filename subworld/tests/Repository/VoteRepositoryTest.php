<?php

namespace App\Tests\Repository;

use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Subworld;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VoteRepositoryTest extends KernelTestCase
{
    public function testVoteCanBeSavedAndRetrieved(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $user = new User();
        $user->setEmail("voter@example.com");
        $user->setUsername("voter");
        $user->setPassword("hashedpassword");
        $entityManager->persist($user);

        $subworld = new Subworld();
        $subworld->setName("Gaming World");
        $subworld->setDescription("A subworld for gaming enthusiasts.");
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user);
        $entityManager->persist($subworld);

        $post = new Post();
        $post->setTitle("Test Post");
        $post->setContent("This is a test post.");
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);
        $post->setSubworld($subworld);
        $entityManager->persist($post);

        $vote = new Vote();
        $vote->setValue(1);
        $vote->setUser($user);
        $vote->setPost($post);
        $entityManager->persist($vote);
        $entityManager->flush();

        $voteRepository = $entityManager->getRepository(Vote::class);
        $savedVote = $voteRepository->findOneBy(['user' => $user, 'post' => $post]);

        $this->assertNotNull($savedVote);
        $this->assertEquals(1, $savedVote->getValue());
    }
}
