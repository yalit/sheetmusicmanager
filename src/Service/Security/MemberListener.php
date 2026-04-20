<?php

namespace App\Service\Security;

use App\Entity\Security\Member;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(entity: Member::class, event: Events::prePersist)]
#[AsEntityListener(entity: Member::class, event: Events::preUpdate)]
class MemberListener
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {}

    public function prePersist(Member $member, PrePersistEventArgs $event): void
    {
        if ($member->getPlainPassword() === null || $member->getPlainPassword() === "") {
            return;
        }

        $this->updatePasswordFromPlainPassword($member, $member->getPlainPassword());
    }

    public function preUpdate(Member $member, PreUpdateEventArgs $event): void
    {
        if ($member->getPlainPassword() === null || $member->getPlainPassword() === "") {
            return;
        }

        $this->updatePasswordFromPlainPassword($member);
    }

    private function updatePasswordFromPlainPassword(Member $member): void
    {
        $password = $this->userPasswordHasher->hashPassword($member, $member->getPlainPassword());
        $member->setPassword($password);
        $member->eraseCredentials();
    }
}
