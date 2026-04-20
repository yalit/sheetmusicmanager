<?php

namespace App\DataFixtures;

use App\Entity\Security\Member;
use App\Entity\WebDAV\WebDavToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WebDavTokenFixtures extends Fixture implements DependentFixtureInterface
{
    // Plain token value for the active admin token — usable in tests / manual testing
    public const ADMIN_PLAIN_TOKEN = 'admin-plain-webdav-token-for-testing';
    public const LIBRARIAN_PLAIN_TOKEN = 'librarian-plain-webdav-token';
    public const CONTRIBUTOR_PLAIN_TOKEN = 'contributor-plain-webdav-token';
    public const EXPIRED_PLAIN_TOKEN = 'expired-plain-webdav-token';

    public function load(ObjectManager $manager): void
    {
        // Admin — active token, expires in 90 days
        $adminToken = (new WebDavToken())
            ->setMember($this->getReference(MemberFixtures::ADMIN_REF, Member::class))
            ->setHashedToken(password_hash(self::ADMIN_PLAIN_TOKEN, PASSWORD_BCRYPT))
            ->setExpiresAt(new \DateTimeImmutable('+90 days'));

        $manager->persist($adminToken);

        // Librarian — active token, expires in 90 days
        $librarianToken = (new WebDavToken())
            ->setMember($this->getReference(MemberFixtures::LIBRARIAN_REF, Member::class))
            ->setHashedToken(password_hash(self::LIBRARIAN_PLAIN_TOKEN, PASSWORD_BCRYPT))
            ->setExpiresAt(new \DateTimeImmutable('+90 days'));

        $manager->persist($librarianToken);

        // Contributor — active token, expires in 90 days
        $contributorToken = (new WebDavToken())
            ->setMember($this->getReference(MemberFixtures::CONTRIBUTOR_REF, Member::class))
            ->setHashedToken(password_hash(self::CONTRIBUTOR_PLAIN_TOKEN, PASSWORD_BCRYPT))
            ->setExpiresAt(new \DateTimeImmutable('+90 days'));

        $manager->persist($contributorToken);

        // Expired member — expired token (expired 30 days ago)
        $expiredToken = (new WebDavToken())
            ->setMember($this->getReference(MemberFixtures::EXPIRED_TOKEN_REF, Member::class))
            ->setHashedToken(password_hash(self::EXPIRED_PLAIN_TOKEN, PASSWORD_BCRYPT))
            ->setExpiresAt(new \DateTimeImmutable('-30 days'));

        $manager->persist($expiredToken);

        // Member — no token
        // NotUsed — no token

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MemberFixtures::class];
    }
}
