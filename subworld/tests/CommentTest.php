<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testCommentCanBeCreated(): void
    {
        $comment = new Comment();
        $comment->setContent("This is a test comment.");
        $comment->setCreatedAt(new \DateTime());

        $this->assertEquals("This is a test comment.", $comment->getContent());
        $this->assertInstanceOf(\DateTime::class, $comment->getCreatedAt());
    }

    public function testCommentHasUserAndPost(): void
    {
        $comment = new Comment();
        $user = new User();
        $post = new Post();

        $comment->setUser($user);
        $comment->setPost($post);

        $this->assertSame($user, $comment->getUser());
        $this->assertSame($post, $comment->getPost());
    }
}
