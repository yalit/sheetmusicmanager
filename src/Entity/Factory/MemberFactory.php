<?php

namespace App\Entity\Factory;

use App\Entity\Member;
use App\Enum\MemberRole;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function createAdmin(string $name, string $email, string $plainPassword): Member
    {
        return $this->create($name, $email, $plainPassword, MemberRole::Admin);
    }

    private function create(string $name, string $email, string $plainPassword, MemberRole $role): Member
    {
        $member = new Member();
        $member->setName($name);
        $member->setEmail($email);
        $member->setPassword($this->passwordHasher->hashPassword($member, $plainPassword));
        $member->setRole($role);

        return $member;
    }
}
