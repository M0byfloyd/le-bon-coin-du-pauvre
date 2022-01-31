<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['USER_EDIT', 'USER_VIEW', 'USER_DELETE','USER_CAN_EDIT'])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'USER_EDIT':
            case 'USER_VIEW':
                if ($user === $subject) {
                    return true;
                }
                if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
                    return true;
                }
                break;
        }

        return false;
    }
}
