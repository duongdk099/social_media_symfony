<?php

namespace App\Security\Voter;

use App\Entity\Subworld;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SubworldVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Subworld;
    }

    protected function voteOnAttribute(string $attribute, $subworld, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        if ($attribute === self::EDIT) {
            return $subworld->getOwner() === $user;
        }

        if ($attribute === self::DELETE) {
            return $subworld->getOwner() === $user;
        }

        return false;
    }
}
