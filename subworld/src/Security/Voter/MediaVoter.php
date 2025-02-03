<?php

namespace App\Security\Voter;

use App\Entity\Media;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MediaVoter extends Voter
{
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof Media;
    }

    protected function voteOnAttribute(string $attribute, $media, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                if ($media->getPost()->getUser() === $user) {
                    return true;
                }

                if (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MODERATOR', $user->getRoles())) {
                    return true;
                }

                break;
        }

        return false;
    }
}
