<?php

namespace App\Security\Voter;

use App\Entity\Setlist\Setlist;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SetlistVoter extends Voter
{
    const INDEX          = 'SETLIST_INDEX';
    const DETAIL         = 'SETLIST_DETAIL';
    const NEW            = 'SETLIST_NEW';
    const EDIT           = 'SETLIST_EDIT';
    const DELETE         = 'SETLIST_DELETE';
    const EXPORT_FORSCORE = 'SETLIST_EXPORT_FORSCORE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ($attribute === self::NEW && $subject === null)
            || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === Setlist::class)
            || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE, self::EXPORT_FORSCORE]) && $subject instanceof Setlist);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::INDEX, self::DETAIL, self::NEW => $this->security->isGranted('ROLE_MEMBER'),
            self::EDIT, self::DELETE,
            self::EXPORT_FORSCORE                => $this->voteOnOwned($subject, $token),
            default                              => false,
        };
    }

    private function voteOnOwned(mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_CONTRIBUTOR')) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface || !$subject instanceof Setlist) {
            return false;
        }

        return $subject->getCreatedBy() === $user->getUserIdentifier();
    }
}
