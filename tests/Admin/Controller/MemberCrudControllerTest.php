<?php

namespace App\Tests\Admin\Controller;

use App\Controller\Admin\MemberCrudController;
use App\Enum\Security\MemberRole;
use App\Tests\Admin\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class MemberCrudControllerTest extends AbstractAdminTestCase
{
    protected function getControllerFqcn(): string
    {
        return MemberCrudController::class;
    }

    public function testIndexAsContributor(): void
    {
        $this->loginAs(MemberRole::Contributor);
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseStatusCodeSame(403);
    }

    /**
     * @return iterable<string, array<MemberRole, bool>>
     */
    public static function getMemberRolesRightsForIndex(): iterable
    {
        return [
            "Admin can view members" => [MemberRole::Admin, true],
            "Librarian can not view members" => [MemberRole::Librarian, false],
            "Contributor can not view members" => [MemberRole::Contributor, false],
            "Member can not view members" => [MemberRole::Member, false],
        ];
    }

    #[DataProvider('getMemberRolesRightsForIndex')]
    public function testIndexAccess(MemberRole $role, bool $isPermitted): void
    {
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array<MemberRole, bool>>
     */
    public static function getMemberRolesRightsForNew(): iterable
    {
        return [
            "Admin can create members" => [MemberRole::Admin, true],
            "Librarian can not create members" => [MemberRole::Librarian, false],
            "Contributor can not create members" => [MemberRole::Contributor, false],
            "Member can not create members" => [MemberRole::Member, false],
        ];
    }

    #[DataProvider('getMemberRolesRightsForNew')]
    public function testNewAccess(MemberRole $role, bool $isPermitted): void
    {
        $this->loginAs($role);
        $this->client->request('GET', $this->generateNewFormUrl());
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getMemberRolesRightsForNew')]
    public function testNewDisplayOnIndex(MemberRole $role, bool $isPermitted): void
    {
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            static::assertGlobalActionExists(Action::NEW);
        } else {
            static::assertGlobalActionNotExists(Action::NEW);
        }
    }

    /**
     * @return iterable<string, array<MemberRole, bool>>
     */
    public static function getMemberRolesRightsForEdit(): iterable
    {
        return [
            "Admin can create members" => [MemberRole::Admin, true],
            "Librarian can not create members" => [MemberRole::Librarian, false],
            "Contributor can not create members" => [MemberRole::Contributor, false],
            "Member can not create members" => [MemberRole::Member, false],
        ];
    }

    #[DataProvider('getMemberRolesRightsForEdit')]
    public function testEditAccess(MemberRole $role, bool $isPermitted): void
    {
        $this->loginAs($role);
        $member = $this->getMember(MemberRole::Member);
        $this->client->request('GET', $this->generateEditFormUrl($member->getId()));

        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getMemberRolesRightsForEdit')]
    public function testEditDisplayOnIndex(MemberRole $role, bool $isPermitted): void
    {
        $member = $this->getMember(MemberRole::Member);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());

        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::EDIT, $member->getId() ?? '');
        }
        // Non-Admin users cannot reach the members index, so no assertion for the false case
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getMemberRolesRightsForDetail(): iterable
    {
        return [
            'Admin can view any member detail'            => [MemberRole::Admin,       true],
            'Librarian cannot view another member detail' => [MemberRole::Librarian,   false],
            'Contributor cannot view another member detail' => [MemberRole::Contributor, false],
            'Member can view own detail'                  => [MemberRole::Member,      true],
        ];
    }

    #[DataProvider('getMemberRolesRightsForDetail')]
    public function testDetailAccess(MemberRole $role, bool $isPermitted): void
    {
        $member = $this->getMember(MemberRole::Member);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($member->getId()));

        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array{MemberRole}>
     */
    public static function getAllMemberRoles(): iterable
    {
        return [
            'Admin can view own detail'       => [MemberRole::Admin],
            'Librarian can view own detail'   => [MemberRole::Librarian],
            'Contributor can view own detail' => [MemberRole::Contributor],
            'Member can view own detail'      => [MemberRole::Member],
        ];
    }

    #[DataProvider('getAllMemberRoles')]
    public function testDetailOwnAccess(MemberRole $role): void
    {
        $member = $this->getMember($role);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($member->getId()));
        static::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getMemberRolesRightsForDelete(): iterable
    {
        return [
            'Admin can delete a member'        => [MemberRole::Admin,       true, true],
            'Librarian cannot delete a member' => [MemberRole::Librarian,   false, false],
            'Contributor cannot delete a member' => [MemberRole::Contributor, false, false],
            'Member cannot delete a member'    => [MemberRole::Member,      false, false],
        ];
    }

    #[DataProvider('getMemberRolesRightsForDelete')]
    public function testDeleteAccess(MemberRole $role, bool $isPermitted, bool $canViewIndex): void
    {
        $member = $this->getMember(MemberRole::Member);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());


        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::DELETE, $member->getId() ?? "");
        } else {
            if ($canViewIndex) {
                self::assertIndexEntityActionNotExists(Action::DELETE, $member->getId() ?? "");
            }
        }
    }
}
