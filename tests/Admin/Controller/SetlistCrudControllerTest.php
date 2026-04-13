<?php

namespace App\Tests\Admin\Controller;

use App\Controller\Admin\SetlistCrudController;
use App\Enum\MemberRole;
use App\Tests\Admin\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class SetlistCrudControllerTest extends AbstractAdminTestCase
{
    protected function getControllerFqcn(): string
    {
        return SetlistCrudController::class;
    }

    public function testIndexAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseRedirects('/login');
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getSetlistRightsForIndex(): iterable
    {
        return [
            'Member can view setlists'      => [MemberRole::Member,      true],
            'Contributor can view setlists' => [MemberRole::Contributor, true],
            'Librarian can view setlists'   => [MemberRole::Librarian,   true],
            'Admin can view setlists'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSetlistRightsForIndex')]
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
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getSetlistRightsForDetail(): iterable
    {
        return [
            'Member can view setlist detail'      => [MemberRole::Member,      true],
            'Contributor can view setlist detail' => [MemberRole::Contributor, true],
            'Librarian can view setlist detail'   => [MemberRole::Librarian,   true],
            'Admin can view setlist detail'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSetlistRightsForDetail')]
    public function testDetailAccess(MemberRole $role, bool $isPermitted): void
    {
        $setlist = $this->getSetlist(MemberRole::Member);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($setlist->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getSetlistRightsForNew(): iterable
    {
        return [
            'Member can create setlists'      => [MemberRole::Member,      true],
            'Contributor can create setlists' => [MemberRole::Contributor, true],
            'Librarian can create setlists'   => [MemberRole::Librarian,   true],
            'Admin can create setlists'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSetlistRightsForNew')]
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

    #[DataProvider('getSetlistRightsForNew')]
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
     * EDIT and DELETE use ownership logic: ROLE_CONTRIBUTOR can edit/delete any setlist,
     * ROLE_MEMBER can only edit/delete their own.
     *
     * @return iterable<string, array{MemberRole, MemberRole, bool}>
     */
    public static function getSetlistRightsForEdit(): iterable
    {
        return [
            'Member can edit own setlist'           => [MemberRole::Member,      MemberRole::Member,      true],
            'Member cannot edit others setlist'     => [MemberRole::Member,      MemberRole::Librarian,   false],
            'Contributor can edit any setlist'      => [MemberRole::Contributor, MemberRole::Member,      true],
            'Librarian can edit any setlist'        => [MemberRole::Librarian,   MemberRole::Member,      true],
            'Admin can edit any setlist'            => [MemberRole::Admin,       MemberRole::Member,      true],
        ];
    }

    #[DataProvider('getSetlistRightsForEdit')]
    public function testEditAccess(MemberRole $role, MemberRole $setlistOwner, bool $isPermitted): void
    {
        $setlist = $this->getSetlist($setlistOwner);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateEditFormUrl($setlist->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getSetlistRightsForEdit')]
    public function testEditDisplayOnIndex(MemberRole $role, MemberRole $setlistOwner, bool $isPermitted): void
    {
        $setlist = $this->getSetlist($setlistOwner);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::EDIT, $setlist->getId() ?? '');
        } else {
            self::assertIndexEntityActionNotExists(Action::EDIT, $setlist->getId() ?? '');
        }
    }

    /**
     * @return iterable<string, array{MemberRole, MemberRole, bool, bool}>
     */
    public static function getSetlistRightsForDelete(): iterable
    {
        return [
            'Member can delete own setlist'       => [MemberRole::Member,      MemberRole::Member,    true,  true],
            'Member cannot delete others setlist' => [MemberRole::Member,      MemberRole::Librarian, false, true],
            'Contributor can delete any setlist'  => [MemberRole::Contributor, MemberRole::Member,    true,  true],
            'Librarian can delete any setlist'    => [MemberRole::Librarian,   MemberRole::Member,    true,  true],
            'Admin can delete any setlist'        => [MemberRole::Admin,       MemberRole::Member,    true,  true],
        ];
    }

    public function testEditFormHasSortableAttributeOnItemsWidget(): void
    {
        $setlist = $this->getSetlist(MemberRole::Member);
        $this->loginAs(MemberRole::Member);
        $this->client->request('GET', $this->generateEditFormUrl($setlist->getId()));

        static::assertResponseIsSuccessful();
        static::assertSelectorExists('[data-collection-table-allow-sort-value="true"]');
    }

    public function testEditFormPrototypeContainsDragHandle(): void
    {
        $setlist = $this->getSetlist(MemberRole::Member);
        $this->loginAs(MemberRole::Member);
        $this->client->request('GET', $this->generateEditFormUrl($setlist->getId()));

        static::assertResponseIsSuccessful();
        static::assertSelectorExists('[data-collection-table-target="prototype"] .drag-handle');
    }

    #[DataProvider('getSetlistRightsForDelete')]
    public function testDeleteAccess(MemberRole $role, MemberRole $setlistOwner, bool $isPermitted, bool $canViewIndex): void
    {
        $setlist = $this->getSetlist($setlistOwner);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());

        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::DELETE, $setlist->getId() ?? '');
        } elseif ($canViewIndex) {
            self::assertIndexEntityActionNotExists(Action::DELETE, $setlist->getId() ?? '');
        }
    }
}
