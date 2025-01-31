<?php

namespace App\Tests\Entity;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationCanBeCreated(): void
    {
        $notification = new Notification();
        $notification->setMessage("New comment on your post.");
        $notification->setCreatedAt(new \DateTime());
        $notification->setIsRead(false);

        $this->assertEquals("New comment on your post.", $notification->getMessage());
        $this->assertFalse($notification->isRead());
    }

    public function testNotificationHasUser(): void
    {
        $notification = new Notification();
        $user = new User();
        $notification->setUser($user);

        $this->assertSame($user, $notification->getUser());
    }
}
