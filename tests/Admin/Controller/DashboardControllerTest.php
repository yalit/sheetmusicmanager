<?php

namespace App\Tests\Admin\Controller;

use App\Entity\Security\Member;
use App\Enum\Security\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class DashboardControllerTest extends WebTestCase
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
        static::assertNotNull($member, "Fixture member '{$email}' not found");
        return $member;
    }

    public function testAdminRedirectsToLoginWhenAnonymous(): void
    {
        $this->client->request(Request::METHOD_GET, '/');
        static::assertResponseRedirects('/login');
    }

    // -------------------------------------------------------------------------
    // User menu
    // -------------------------------------------------------------------------

    public static function getAllMemberRoles(): iterable
    {
        return [
            'Admin'       => [MemberRole::Admin],
            'Librarian'   => [MemberRole::Librarian],
            'Contributor' => [MemberRole::Contributor],
            'Member'      => [MemberRole::Member],
        ];
    }

    #[DataProvider('getAllMemberRoles')]
    public function testUserMenuContainsViewProfileLinkForOwnProfile(MemberRole $role): void
    {
        $member = $this->getMember($role);
        $this->client->loginUser($member);
        $this->client->request(Request::METHOD_GET, '/');
        $this->client->followRedirect();

        static::assertResponseIsSuccessful();

        $profileUrl = static::getContainer()->get('router')
            ->generate('admin_member_detail', ['entityId' => $member->getId()]);

        static::assertStringContainsString($profileUrl, (string) $this->client->getResponse()->getContent());
    }
}
