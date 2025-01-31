<?php

namespace App\Tests\Entity;

use App\Entity\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testRoleCanBeCreated(): void
    {
        $role = new Role();
        $role->setName("ROLE_ADMIN");

        $this->assertEquals("ROLE_ADMIN", $role->getName());
    }
}
