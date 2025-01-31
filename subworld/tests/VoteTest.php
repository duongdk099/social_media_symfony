<?php

namespace App\Tests\Entity;

use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class VoteTest extends TestCase
{
    public function testVoteCanBeCreated(): void
    {
        $vote = new Vote();
        $vote->setValue(1);

        $this->assertEquals(1, $vote->getValue());
    }

    public function testVoteHasUserAndPost(): void
    {
        $vote = new Vote();
        $user = new User();
        $post = new Post();
        $vote->setUser($user);
        $vote->setPost($post);

        $this->assertSame($user, $vote->getUser());
        $this->assertSame($post, $vote->getPost());
    }
}
