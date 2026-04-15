<?php

namespace App\Security\Voter;

use App\Entity\Sheet\Person;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PersonVoter extends Voter
{
    const INDEX  = 'PERSON_INDEX';
    const DETAIL = 'PERSON_DETAIL';
    const NEW    = 'PERSON_NEW';
    const EDIT   = 'PERSON_EDIT';
    const DELETE = 'PERSON_DELETE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ($attribute === self::NEW && $subject === null)
            || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === Person::class)
            || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE]) && $subject instanceof Person);
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
