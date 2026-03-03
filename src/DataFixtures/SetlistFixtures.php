<?php

namespace App\DataFixtures;

use App\Entity\Setlist;
use App\Enum\MemberRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class SetlistFixtures extends Fixture implements DependentFixtureInterface
{
    public const MEMBER_SETLIST_REF      = 'setlist-member';
    public const CONTRIBUTOR_SETLIST_REF = 'setlist-contributor';
    public const LIBRARIAN_SETLIST_REF   = 'setlist-librarian';
    public const ADMIN_SETLIST_REF   = 'setlist-admin';

    public function __construct(private readonly Connection $connection) {}

    public function getDependencies(): array
    {
        return [MemberFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $setlists = [
            ['Member Setlist',      MemberRole::Member->value.'@sheetmusic.test',      self::MEMBER_SETLIST_REF],
            ['Contributor Setlist', MemberRole::Contributor->value.'@sheetmusic.test', self::CONTRIBUTOR_SETLIST_REF],
            ['Librarian Setlist',   MemberRole::Librarian->value.'@sheetmusic.test',   self::LIBRARIAN_SETLIST_REF],
            ['Admin Setlist',   MemberRole::Admin->value.'@sheetmusic.test',   self::ADMIN_SETLIST_REF],
        ];

        foreach ($setlists as [$title, $ownerEmail, $ref]) {
            $setlist = (new Setlist())->setTitle($title);
            $manager->persist($setlist);
            $manager->flush();

            $setlist->setCreatedBy($ownerEmail);
            $setlist->setUpdatedBy($ownerEmail);
            $manager->persist($setlist);
            $manager->flush();

            $this->addReference($ref, $setlist);
        }

        $manager->clear();
    }
}
