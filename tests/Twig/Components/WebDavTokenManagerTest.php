<?php

namespace App\Tests\Twig\Components;

use App\Entity\Security\Member;
use App\Entity\WebDAV\WebDavToken;
use App\Enum\Security\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

final class WebDavTokenManagerTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function getMember(MemberRole $role): Member
    {
        $email  = "{$role->value}@sheetmusic.test";
        $member = $this->em()->getRepository(Member::class)->findOneBy(['email' => $email]);
        static::assertNotNull($member);
        return $member;
    }

    private function freshMember(MemberRole $role): Member
    {
        $this->em()->clear();
        return $this->getMember($role);
    }

    // -------------------------------------------------------------------------
    // Initial render
    // -------------------------------------------------------------------------

    public function testRendersNoTokenStateWhenMemberHasNoToken(): void
    {
        $member    = $this->getMember(MemberRole::Member);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        static::assertNull($member->getWebDavToken());
        $html = $component->render()->toString();

        static::assertStringContainsString('data-no-token', $html);
        static::assertStringNotContainsString('data-token-expiry', $html);
    }

    public function testRendersTokenExpiryWhenMemberHasActiveToken(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        static::assertNotNull($member->getWebDavToken());
        $html = $component->render()->toString();

        static::assertStringNotContainsString('data-no-token', $html);
        static::assertStringContainsString('data-token-expiry', $html);
    }

    public function testDoesNotShowPlainTokenOnInitialRender(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $html = $component->render()->toString();

        static::assertStringNotContainsString('data-plain-token', $html);
    }

    // -------------------------------------------------------------------------
    // Generate
    // -------------------------------------------------------------------------

    public function testGenerateCreatesTokenForMemberWithNoToken(): void
    {
        $member    = $this->getMember(MemberRole::Member);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('generate');

        $this->em()->clear();
        $updated = $this->getMember(MemberRole::Member);
        static::assertNotNull($updated->getWebDavToken());
    }

    public function testGenerateShowsPlainTokenAfterCreation(): void
    {
        $member    = $this->getMember(MemberRole::Member);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $html = $component->call('generate')->render()->toString();

        static::assertStringContainsString('data-plain-token', $html);
    }

    public function testGenerateRespectsConfiguredTtlDays(): void
    {
        $member    = $this->getMember(MemberRole::Member);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->set('ttlDays', 30)->call('generate');

        $this->em()->clear();
        $token    = $this->getMember(MemberRole::Member)->getWebDavToken();
        $daysLeft = (int) (new \DateTimeImmutable())->diff($token->getExpiresAt())->days;

        static::assertEqualsWithDelta(30, $daysLeft, 1);
    }

    public function testGenerateReplacesExistingToken(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $oldToken  = $member->getWebDavToken();
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('generate');

        $this->em()->clear();
        $newToken = $this->getMember(MemberRole::Admin)->getWebDavToken();
        static::assertNotSame($oldToken->getId(), $newToken->getId());
    }

    // -------------------------------------------------------------------------
    // Recycle
    // -------------------------------------------------------------------------

    public function testRecycleUpdatesHashedTokenInPlace(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $oldHash   = $member->getWebDavToken()->getHashedToken();
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('renew');

        $this->em()->clear();
        $newHash = $this->getMember(MemberRole::Admin)->getWebDavToken()->getHashedToken();
        static::assertNotSame($oldHash, $newHash);
    }

    public function testRecycleKeepsSameTokenId(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $tokenId   = $member->getWebDavToken()->getId();
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('renew');

        $this->em()->clear();
        $renewedId = $this->getMember(MemberRole::Admin)->getWebDavToken()->getId();
        static::assertSame($tokenId, $renewedId);
    }

    public function testRecycleResetsExpiryWithConfiguredTtl(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->set('ttlDays', 7)->call('renew');

        $this->em()->clear();
        $token    = $this->getMember(MemberRole::Admin)->getWebDavToken();
        $daysLeft = (int) (new \DateTimeImmutable())->diff($token->getExpiresAt())->days;

        static::assertEqualsWithDelta(7, $daysLeft, 1);
    }

    public function testRecycleShowsPlainToken(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $html = $component->call('renew')->render()->toString();

        static::assertStringContainsString('data-plain-token', $html);
    }

    // -------------------------------------------------------------------------
    // Revoke
    // -------------------------------------------------------------------------

    public function testRevokeDeletesToken(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('revoke');

        static::assertNull($this->getMember(MemberRole::Admin)->getWebDavToken());
    }

    public function testRevokeShowsNoTokenState(): void
    {
        $member    = $this->getMember(MemberRole::Admin);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $html = $component->call('revoke')->render()->toString();

        static::assertStringContainsString('data-no-token', $html);
        static::assertStringNotContainsString('data-plain-token', $html);
    }

    // -------------------------------------------------------------------------
    // Dismiss
    // -------------------------------------------------------------------------

    public function testDismissClearsPlainToken(): void
    {
        $member    = $this->getMember(MemberRole::Member);
        $component = $this->createLiveComponent('WebDavTokenManager', ['member' => $member])
            ->actingAs($member);

        $component->call('generate');
        $html = $component->call('dismiss')->render()->toString();

        static::assertStringNotContainsString('data-plain-token', $html);
    }
}
