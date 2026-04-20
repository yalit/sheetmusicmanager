<?php

namespace App\Repository;

use App\Entity\Security\Member;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends BaseRepository<Member>
 */
class MemberRepository extends BaseRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
        if (!$user instanceof Member) {
            throw new \InvalidArgumentException('User must be an instance of Member');
        }

        $user->setPassword($newHashedPassword);
        $this->save($user);
    }
}
