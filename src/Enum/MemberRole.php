<?php

namespace App\Enum;

enum MemberRole: string
{
    case Member = 'ROLE_MEMBER';
    case Contributor = 'ROLE_CONTRIBUTOR';
    case Librarian = 'ROLE_LIBRARIAN';
    case Admin = 'ROLE_ADMIN';

    /**
     * Returns label => value choices for all roles.
     *
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $role) {
            $choices[$role->name] = $role;
        }
        return $choices;
    }
}
