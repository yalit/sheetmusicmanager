<?php

namespace App\Security\Voter;

use App\Entity\Sheet\Sheet;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SheetVoter extends Voter
{
    const INDEX  = 'SHEET_INDEX';
    const DETAIL = 'SHEET_DETAIL';
    const NEW    = 'SHEET_NEW';
    const EDIT   = 'SHEET_EDIT';
    const DELETE = 'SHEET_DELETE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ($attribute === self::NEW && $subject === null)
            || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === Sheet::class)
            || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE]) && $subject instanceof Sheet);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::INDEX => $this->security->isGranted('ROLE_MEMBER'),
            self::EDIT                => $this->security->isGranted('ROLE_CONTRIBUTOR'),
            self::NEW, self::DELETE   => $this->security->isGranted('ROLE_LIBRARIAN'),
            default                   => false,
        };
    }
}
