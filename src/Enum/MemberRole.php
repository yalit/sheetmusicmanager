<?php

namespace App\Enum;

enum MemberRole: string
{
    case Member = 'member';
    case Contributor = 'contributor';
    case Librarian = 'librarian';
    case Admin = 'admin';

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
