<?php

namespace App\Tests\Admin\Controller;

use App\Controller\Admin\PersonTypeCrudController;
use App\DataFixtures\PersonTypeFixtures;
use App\Enum\Security\MemberRole;
use App\Tests\Admin\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class PersonTypeCrudControllerTest extends AbstractAdminTestCase
{
    protected function getControllerFqcn(): string
    {
        return PersonTypeCrudController::class;
    }

    public function testIndexAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseRedirects('/login');
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getPersonTypeRightsForIndex(): iterable
    {
        return [
            'Member can view person types'      => [MemberRole::Member,      true],
            'Contributor can view person types' => [MemberRole::Contributor, true],
            'Librarian can view person types'   => [MemberRole::Librarian,   true],
            'Admin can view person types'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonTypeRightsForIndex')]
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
    public static function getPersonTypeRightsForDetail(): iterable
    {
        return [
            'Member can view person type detail'      => [MemberRole::Member,      true],
            'Contributor can view person type detail' => [MemberRole::Contributor, true],
            'Librarian can view person type detail'   => [MemberRole::Librarian,   true],
            'Admin can view person type detail'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonTypeRightsForDetail')]
    public function testDetailAccess(MemberRole $role, bool $isPermitted): void
    {
        $personType = $this->getPersonType(PersonTypeFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($personType->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getPersonTypeRightsForNew(): iterable
    {
        return [
            'Member cannot create person types'      => [MemberRole::Member,      false],
            'Contributor cannot create person types' => [MemberRole::Contributor, false],
            'Librarian can create person types'      => [MemberRole::Librarian,   true],
            'Admin can create person types'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonTypeRightsForNew')]
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

    #[DataProvider('getPersonTypeRightsForNew')]
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
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getPersonTypeRightsForEdit(): iterable
    {
        return [
            'Member cannot edit person types'      => [MemberRole::Member,      false],
            'Contributor cannot edit person types' => [MemberRole::Contributor, false],
            'Librarian can edit person types'      => [MemberRole::Librarian,   true],
            'Admin can edit person types'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonTypeRightsForEdit')]
    public function testEditAccess(MemberRole $role, bool $isPermitted): void
    {
        $personType = $this->getPersonType(PersonTypeFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateEditFormUrl($personType->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getPersonTypeRightsForEdit')]
    public function testEditDisplayOnIndex(MemberRole $role, bool $isPermitted): void
    {
        $personType = $this->getPersonType(PersonTypeFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::EDIT, $personType->getId() ?? '');
        } else {
            self::assertIndexEntityActionNotExists(Action::EDIT, $personType->getId() ?? '');
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool, bool}>
     */
    public static function getPersonTypeRightsForDelete(): iterable
    {
        return [
            'Member cannot delete person types'      => [MemberRole::Member,      false, true],
            'Contributor cannot delete person types' => [MemberRole::Contributor, false, true],
            'Librarian can delete person types'      => [MemberRole::Librarian,   true,  true],
            'Admin can delete person types'          => [MemberRole::Admin,       true,  true],
        ];
    }

    #[DataProvider('getPersonTypeRightsForDelete')]
    public function testDeleteAccess(MemberRole $role, bool $isPermitted, bool $canViewIndex): void
    {
        $personType = $this->getPersonType(PersonTypeFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());

        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::DELETE, $personType->getId() ?? '');
        } elseif ($canViewIndex) {
            self::assertIndexEntityActionNotExists(Action::DELETE, $personType->getId() ?? '');
        }
    }
}
