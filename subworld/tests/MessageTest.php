<?php

namespace App\Tests\Entity;

use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessageCanBeCreated(): void
    {
        $message = new Message();
        $message->setContent("Hello, this is a test message.");
        $message->setCreatedAt(new \DateTime());

        $this->assertEquals("Hello, this is a test message.", $message->getContent());
        $this->assertInstanceOf(\DateTime::class, $message->getCreatedAt());
    }

    public function testMessageHasSenderAndReceiver(): void
    {
        $message = new Message();
        $sender = new User();
        $receiver = new User();
        $message->setSender($sender);
        $message->setReceiver($receiver);

        $this->assertSame($sender, $message->getSender());
        $this->assertSame($receiver, $message->getReceiver());
    }
}
