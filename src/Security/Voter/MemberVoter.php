<?php

namespace App\Security\Voter;

use App\Entity\Member;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Member>
 */
class MemberVoter extends Voter
{
    const INDEX = 'MEMBER_INDEX';
    const DETAIL = 'MEMBER_DETAIL';
    const NEW = 'MEMBER_NEW';
    const EDIT = 'MEMBER_EDIT';
    const DELETE = 'MEMBER_DELETE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ($attribute === self::NEW && $subject === null)
            || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === Member::class)
            || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE]) && $subject instanceof Member);
    }

    /**
     * @param UserInterface $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
