<?php

namespace App\Tests\Security\Voter;

use App\Entity\Security\Member;
use App\Enum\Security\MemberRole;
use App\Security\Voter\MemberVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MemberVoterTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

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

    private function detailUrl(Member $member): string
    {
        return static::getContainer()->get('router')->generate('admin_member_detail', ['entityId' => $member->getId()]);
    }

    // -------------------------------------------------------------------------
    // DETAIL — self access
    // -------------------------------------------------------------------------

    public function testMemberCanViewTheirOwnDetailPage(): void
    {
        $member = $this->getMember(MemberRole::Member);
        $this->client->loginUser($member);

        $this->client->request('GET', $this->detailUrl($member));

        static::assertResponseIsSuccessful();
    }

    public function testMemberCannotViewAnotherMembersDetailPage(): void
    {
        $viewer = $this->getMember(MemberRole::Member);
        $target = $this->getMember(MemberRole::Librarian);
        $this->client->loginUser($viewer);

        $this->client->request('GET', $this->detailUrl($target));

        static::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanViewAnyMemberDetailPage(): void
    {
        $admin  = $this->getMember(MemberRole::Admin);
        $target = $this->getMember(MemberRole::Member);
        $this->client->loginUser($admin);

        $this->client->request('GET', $this->detailUrl($target));

        static::assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // DETAIL self-access does NOT extend to EDIT
    // -------------------------------------------------------------------------

    public function testMemberCannotEditTheirOwnProfile(): void
    {
        $member = $this->getMember(MemberRole::Member);
        $this->client->loginUser($member);

        $url = static::getContainer()->get('router')->generate('admin_member_edit', ['entityId' => $member->getId()]);
        $this->client->request('GET', $url);

        static::assertResponseStatusCodeSame(403);
    }
}
