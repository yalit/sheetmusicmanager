<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Enum\MemberRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberFixtures extends Fixture
{
    public const MEMBER_REF      = 'member-member';
    public const CONTRIBUTOR_REF = 'member-contributor';
    public const LIBRARIAN_REF   = 'member-librarian';
    public const ADMIN_REF       = 'member-admin';

    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        foreach ([
            [MemberRole::Member,      MemberRole::Member->value.'@sheetmusic.test',      self::MEMBER_REF],
            [MemberRole::Contributor, MemberRole::Contributor->value.'@sheetmusic.test', self::CONTRIBUTOR_REF],
            [MemberRole::Librarian,   MemberRole::Librarian->value.'@sheetmusic.test',   self::LIBRARIAN_REF],
            [MemberRole::Admin,       MemberRole::Admin->value.'@sheetmusic.test',       self::ADMIN_REF],
        ] as [$role, $email, $ref]) {
            $member = (new Member())
                ->setEmail($email)
                ->setName($role->name)
                ->setRole($role);
            $member->setPassword($this->hasher->hashPassword($member, 'password'));

            $manager->persist($member);
            $this->addReference($ref, $member);
        }

        $manager->flush();
    }
}
