<?php

namespace App\Tests\Entity;

use App\Entity\Subworld;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class SubworldTest extends TestCase
{
    public function testSubworldCanBeCreated(): void
    {
        $subworld = new Subworld();
        $subworld->setName("Test Subworld");
        $subworld->setDescription("This is a test subworld.");
        $subworld->setCreatedAt(new \DateTime());

        $this->assertEquals("Test Subworld", $subworld->getName());
        $this->assertEquals("This is a test subworld.", $subworld->getDescription());
        $this->assertInstanceOf(\DateTime::class, $subworld->getCreatedAt());
    }

    public function testSubworldHasOwner(): void
    {
        $subworld = new Subworld();
        $user = new User();
        $subworld->setOwner($user);

        $this->assertSame($user, $subworld->getOwner());
    }
}
