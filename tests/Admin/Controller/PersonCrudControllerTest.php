<?php

namespace App\Tests\Admin\Controller;

use App\Controller\Admin\PersonCrudController;
use App\DataFixtures\PersonFixtures;
use App\Enum\MemberRole;
use App\Tests\Admin\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class PersonCrudControllerTest extends AbstractAdminTestCase
{
    protected function getControllerFqcn(): string
    {
        return PersonCrudController::class;
    }

    public function testIndexAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseRedirects('/login');
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getPersonRightsForIndex(): iterable
    {
        return [
            'Member can view persons'      => [MemberRole::Member,      true],
            'Contributor can view persons' => [MemberRole::Contributor, true],
            'Librarian can view persons'   => [MemberRole::Librarian,   true],
            'Admin can view persons'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonRightsForIndex')]
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
    public static function getPersonRightsForDetail(): iterable
    {
        return [
            'Member can view person detail'      => [MemberRole::Member,      true],
            'Contributor can view person detail' => [MemberRole::Contributor, true],
            'Librarian can view person detail'   => [MemberRole::Librarian,   true],
            'Admin can view person detail'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonRightsForDetail')]
    public function testDetailAccess(MemberRole $role, bool $isPermitted): void
    {
        $person = $this->getPerson(PersonFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($person->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getPersonRightsForNew(): iterable
    {
        return [
            'Member cannot create persons'      => [MemberRole::Member,      false],
            'Contributor cannot create persons' => [MemberRole::Contributor, false],
            'Librarian can create persons'      => [MemberRole::Librarian,   true],
            'Admin can create persons'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonRightsForNew')]
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

    #[DataProvider('getPersonRightsForNew')]
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
    public static function getPersonRightsForEdit(): iterable
    {
        return [
            'Member cannot edit persons'      => [MemberRole::Member,      false],
            'Contributor cannot edit persons' => [MemberRole::Contributor, false],
            'Librarian can edit persons'      => [MemberRole::Librarian,   true],
            'Admin can edit persons'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getPersonRightsForEdit')]
    public function testEditAccess(MemberRole $role, bool $isPermitted): void
    {
        $person = $this->getPerson(PersonFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateEditFormUrl($person->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getPersonRightsForEdit')]
    public function testEditDisplayOnIndex(MemberRole $role, bool $isPermitted): void
    {
        $person = $this->getPerson(PersonFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::EDIT, $person->getId() ?? '');
        } else {
            self::assertIndexEntityActionNotExists(Action::EDIT, $person->getId() ?? '');
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool, bool}>
     */
    public static function getPersonRightsForDelete(): iterable
    {
        return [
            'Member cannot delete persons'      => [MemberRole::Member,      false, true],
            'Contributor cannot delete persons' => [MemberRole::Contributor, false, true],
            'Librarian can delete persons'      => [MemberRole::Librarian,   true,  true],
            'Admin can delete persons'          => [MemberRole::Admin,       true,  true],
        ];
    }

    #[DataProvider('getPersonRightsForDelete')]
    public function testDeleteAccess(MemberRole $role, bool $isPermitted, bool $canViewIndex): void
    {
        $person = $this->getPerson(PersonFixtures::NAMES[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());

        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::DELETE, $person->getId() ?? '');
        } elseif ($canViewIndex) {
            self::assertIndexEntityActionNotExists(Action::DELETE, $person->getId() ?? '');
        }
    }
}
