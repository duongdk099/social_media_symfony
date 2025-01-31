<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): void
    {
        $user = new User();
        $user->setEmail("test@example.com");
        $user->setUsername("testuser");
        $user->setPassword("hashedpassword");
        $this->assertEquals("test@example.com", $user->getEmail());
        $this->assertEquals("testuser", $user->getUsername());
        $this->assertEquals("hashedpassword", $user->getPassword());
    }

    public function testRolesDefaultToUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
