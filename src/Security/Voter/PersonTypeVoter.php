<?php

namespace App\Security\Voter;

use App\Entity\Sheet\PersonType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PersonTypeVoter extends Voter
{
    const INDEX  = 'PERSON_TYPE_INDEX';
    const DETAIL = 'PERSON_TYPE_DETAIL';
    const NEW    = 'PERSON_TYPE_NEW';
    const EDIT   = 'PERSON_TYPE_EDIT';
    const DELETE = 'PERSON_TYPE_DELETE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ($attribute === self::NEW && $subject === null)
            || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === PersonType::class)
            || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE]) && $subject instanceof PersonType);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::INDEX, self::DETAIL            => $this->security->isGranted('ROLE_MEMBER'),
            self::NEW, self::EDIT, self::DELETE  => $this->security->isGranted('ROLE_LIBRARIAN'),
            default                              => false,
        };
    }
}
