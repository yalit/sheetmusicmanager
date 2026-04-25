<?php

namespace App\Tests\Entity\WebDAV\Factory;

use App\Entity\Security\Member;
use App\Entity\WebDAV\Factory\WebDavTokenFactory;
use App\Entity\WebDAV\WebDavToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class WebDavTokenFactoryTest extends TestCase
{
    private WebDavTokenFactory $factory;

    protected function setUp(): void
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_token');
        $this->factory = new WebDavTokenFactory($hasher);
    }

    // -------------------------------------------------------------------------
    // new()
    // -------------------------------------------------------------------------

    public function testNewReturnsWebDavToken(): void
    {
        $token = $this->factory->new(new Member(), 90);
        static::assertInstanceOf(WebDavToken::class, $token);
    }

    public function testNewSetsNonEmptyPlainToken(): void
    {
        $token = $this->factory->new(new Member(), 90);
        static::assertNotEmpty($token->getPlainToken());
    }

    public function testNewSetsHashedToken(): void
    {
        $token = $this->factory->new(new Member(), 90);
        static::assertSame('hashed_token', $token->getHashedToken());
    }

    public function testNewSetsExpiryFromTtl(): void
    {
        $token    = $this->factory->new(new Member(), 30);
        $daysLeft = (int) (new \DateTimeImmutable())->diff($token->getExpiresAt())->days;
        static::assertEqualsWithDelta(30, $daysLeft, 1);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function testUpdateKeepsSameTokenInstance(): void
    {
        $member   = $this->memberWithToken();
        $existing = $member->getWebDavToken();

        $returned = $this->factory->update($member, 7);

        static::assertSame($existing, $returned);
    }

    public function testUpdateReplacesHashedToken(): void
    {
        $member = $this->memberWithToken('old_hash');

        $this->factory->update($member, 7);

        static::assertSame('hashed_token', $member->getWebDavToken()->getHashedToken());
    }

    public function testUpdateSetsNonEmptyPlainToken(): void
    {
        $member = $this->memberWithToken();

        $this->factory->update($member, 7);

        static::assertNotEmpty($member->getWebDavToken()->getPlainToken());
    }

    public function testUpdateResetsExpiryWithNewTtl(): void
    {
        $member = $this->memberWithToken();

        $this->factory->update($member, 7);

        $daysLeft = (int) (new \DateTimeImmutable())->diff($member->getWebDavToken()->getExpiresAt())->days;
        static::assertEqualsWithDelta(7, $daysLeft, 1);
    }

    public function testUpdateThrowsWhenMemberHasNoToken(): void
    {
        $this->expectException(\Exception::class);
        $this->factory->update(new Member(), 7);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function memberWithToken(string $hashedToken = 'some_hash'): Member
    {
        $token = (new WebDavToken())
            ->setHashedToken($hashedToken)
            ->setExpiresAt(new \DateTimeImmutable('+90 days'));

        $member = new Member();
        $member->setWebDavToken($token);

        return $member;
    }
}
