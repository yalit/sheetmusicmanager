<?php

namespace App\Entity\WebDAV\Factory;

use App\Entity\Security\Member;
use App\Entity\WebDAV\WebDavToken;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class WebDavTokenFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function new(Member $member, int $ttlExpiryDays): WebDavToken
    {
        $token = new WebDavToken();

        $token->setPlainToken(static::generateToken());
        $token->setHashedToken($this->passwordHasher->hashPassword($member, $token->getPlainToken()));
        $token->setExpiresAt((new DateTimeImmutable())->add(new DateInterval("P{$ttlExpiryDays}D")));

        return $token;
    }

    public function update(Member $member, int $ttlExpiryDays): WebDavToken
    {
        $token = $member->getWebDavToken();

        if (!$token) {
            throw new Exception("Can't find webdav token");
        }

        $token->setPlainToken(static::generateToken());
        $token->setHashedToken($this->passwordHasher->hashPassword($member, $token->getPlainToken()));
        $token->setExpiresAt((new DateTimeImmutable())->add(new DateInterval("P{$ttlExpiryDays}D")));

        return $token;
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
