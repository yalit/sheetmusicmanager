<?php

namespace App\Service\WebDAV;

use App\Entity\Security\Member;
use App\Entity\WebDAV\Factory\WebDavTokenFactory;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class WebDAVTokenHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private WebDAVTokenFactory     $factory,
    ) {}

    public function new(Member $member, int $ttlExpiryDays): string
    {
        $this->revoke($member);

        $token = $this->factory->new($member, $ttlExpiryDays);
        $plainToken = $token->getPlainToken();

        $member->setWebDavToken($token);

        $this->em->persist($token);
        $this->em->persist($member);
        $this->em->flush();
        return $plainToken;
    }

    public function renew(Member $member, int $ttlExpiryDays): string
    {
        $token = $this->factory->update($member, $ttlExpiryDays);
        $this->em->persist($token);
        $this->em->flush();

        return $token->getPlainToken();
    }

    public function revoke(Member $member): void
    {
        $token = $member->getWebDavToken();
        $member->setWebDavToken(null);
        $this->em->persist($member);
        if ($token) {
            $this->em->remove($token);
        }

        $this->em->flush();
    }
}
