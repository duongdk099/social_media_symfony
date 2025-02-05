<?php

namespace App\Security\Voter;

use App\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageVoter extends Voter
{
    public const VIEW = 'view';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $message): bool
    {
        return in_array($attribute, [self::VIEW, self::DELETE]) && $message instanceof Message;
    }

    protected function voteOnAttribute(string $attribute, $message, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $message->getSender() === $user || $message->getReceiver() === $user;

            case self::DELETE:
                return $message->getSender() === $user;
        }

        return false;
    }
}
