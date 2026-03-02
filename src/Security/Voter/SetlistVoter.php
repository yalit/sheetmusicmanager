<?php

namespace App\Security\Voter;

use App\Entity\Setlist;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SetlistVoter extends Voter
{
    const EDIT   = 'SETLIST_EDIT';
    const DELETE = 'SETLIST_DELETE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Setlist;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Contributors and above can manage all setlists
        if ($this->security->isGranted('ROLE_CONTRIBUTOR')) {
            return true;
        }

        // Members can only edit/delete their own
        return $subject->getCreatedBy() === $user->getUserIdentifier();
    }
}
