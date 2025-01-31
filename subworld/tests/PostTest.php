<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPostCanBeCreated(): void
    {
        $post = new Post();
        $post->setTitle("Test Post");
        $post->setContent("This is a test post content.");
        $post->setCreatedAt(new \DateTime());

        $this->assertEquals("Test Post", $post->getTitle());
        $this->assertEquals("This is a test post content.", $post->getContent());
        $this->assertInstanceOf(\DateTime::class, $post->getCreatedAt());
    }

    public function testPostHasUser(): void
    {
        $post = new Post();
        $user = new User();
        $post->setUser($user);
        $this->assertSame($user, $post->getUser());
    }
}
