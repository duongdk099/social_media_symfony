<?php

namespace App\Tests\Entity;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function testReportCanBeCreated(): void
    {
        $report = new Report();
        $report->setReason("Inappropriate content");
        $report->setCreatedAt(new \DateTime());

        $this->assertEquals("Inappropriate content", $report->getReason());
        $this->assertInstanceOf(\DateTime::class, $report->getCreatedAt());
    }

    public function testReportHasUserAndPostOrComment(): void
    {
        $report = new Report();
        $user = new User();
        $post = new Post();
        $comment = new Comment();
        $report->setUser($user);
        $report->setPost($post);
        $report->setComment($comment);

        $this->assertSame($user, $report->getUser());
        $this->assertSame($post, $report->getPost());
        $this->assertSame($comment, $report->getComment());
    }
}
