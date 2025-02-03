<?php

namespace App\Security\Voter;

use App\Entity\Vote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class VoteVoter extends Voter
{
    public const DELETE = 'delete';

    protected function supports(string $attribute, $vote): bool
    {
        return $attribute === self::DELETE && $vote instanceof Vote;
    }

    protected function voteOnAttribute(string $attribute, $vote, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $vote->getUser() === $user;
    }
}
