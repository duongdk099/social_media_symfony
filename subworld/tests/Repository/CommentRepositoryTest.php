<?php

namespace App\Tests\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Subworld;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentRepositoryTest extends KernelTestCase
{
    public function testCommentCanBeSavedAndRetrieved(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail("commenter@example.com");
        $user->setUsername("commenter");
        $user->setPassword("hashedpassword");
        $entityManager->persist($user);

        $subworld = new Subworld();
        $subworld->setName("Tech World");
        $subworld->setDescription("A subworld for tech enthusiasts.");
        $subworld->setCreatedAt(new \DateTime());
        $subworld->setOwner($user);
        $entityManager->persist($subworld);

        $post = new Post();
        $post->setTitle("Post for Comment");
        $post->setContent("Post content.");
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);
        $post->setSubworld($subworld);
        $entityManager->persist($post);

        $comment = new Comment();
        $comment->setContent("This is a test comment.");
        $comment->setCreatedAt(new \DateTime());
        $comment->setUser($user);
        $comment->setPost($post);
        $entityManager->persist($comment);
        $entityManager->flush();

        $commentRepository = $entityManager->getRepository(Comment::class);
        $savedComment = $commentRepository->findOneBy(['content' => 'This is a test comment.']);
        $this->assertNotNull($savedComment);
        $this->assertEquals("This is a test comment.", $savedComment->getContent());
        $this->assertSame($post, $savedComment->getPost());
    }
}
