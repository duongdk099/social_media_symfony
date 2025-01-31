<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    public function testMediaCanBeCreated(): void
    {
        $media = new Media();
        $media->setUrl("https://example.com/image.jpg");
        $media->setType("image");
        $this->assertEquals("https://example.com/image.jpg", $media->getUrl());
        $this->assertEquals("image", $media->getType());
    }

    public function testMediaHasPost(): void
    {
        $media = new Media();
        $post = new Post();
        $media->setPost($post);

        $this->assertSame($post, $media->getPost());
    }
}
