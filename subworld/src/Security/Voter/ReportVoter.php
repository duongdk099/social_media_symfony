<?php

namespace App\Security\Voter;

use App\Entity\Report;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportVoter extends Voter
{
    public const VIEW = 'view';
    public const DELETE = 'delete';
    public const CREATE = 'create';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::DELETE, self::CREATE]) && $subject instanceof Report;
    }

    protected function voteOnAttribute(string $attribute, $report, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return true;

            case self::VIEW:
                return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MODERATOR', $user->getRoles());

            case self::DELETE:
                return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MODERATOR', $user->getRoles());
        }

        return false;
    }
}
