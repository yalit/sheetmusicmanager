<?php

namespace App\Tests\Admin\Controller;

use App\Controller\Admin\SheetCrudController;
use App\DataFixtures\SheetFixtures;
use App\Enum\MemberRole;
use App\Tests\Admin\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class SheetCrudControllerTest extends AbstractAdminTestCase
{
    protected function getControllerFqcn(): string
    {
        return SheetCrudController::class;
    }

    public function testIndexAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseRedirects('/login');
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getSheetRightsForIndex(): iterable
    {
        return [
            'Member can view sheets'      => [MemberRole::Member,      true],
            'Contributor can view sheets' => [MemberRole::Contributor, true],
            'Librarian can view sheets'   => [MemberRole::Librarian,   true],
            'Admin can view sheets'       => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSheetRightsForIndex')]
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
    public static function getSheetRightsForDetail(): iterable
    {
        return [
            'Member can view sheet detail'      => [MemberRole::Member,      false],
            'Contributor can view sheet detail' => [MemberRole::Contributor, false],
            'Librarian can view sheet detail'   => [MemberRole::Librarian,   false],
            'Admin can view sheet detail'       => [MemberRole::Admin,       false],
        ];
    }

    #[DataProvider('getSheetRightsForDetail')]
    public function testDetailAccess(MemberRole $role, bool $isPermitted): void
    {
        $sheet = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateDetailUrl($sheet->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool}>
     */
    public static function getSheetRightsForNew(): iterable
    {
        return [
            'Member cannot create sheets'      => [MemberRole::Member,      false],
            'Contributor cannot create sheets' => [MemberRole::Contributor, false],
            'Librarian can create sheets'      => [MemberRole::Librarian,   true],
            'Admin can create sheets'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSheetRightsForNew')]
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

    #[DataProvider('getSheetRightsForNew')]
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
    public static function getSheetRightsForEdit(): iterable
    {
        return [
            'Member cannot edit sheets'      => [MemberRole::Member,      false],
            'Contributor can edit sheets'    => [MemberRole::Contributor, true],
            'Librarian can edit sheets'      => [MemberRole::Librarian,   true],
            'Admin can edit sheets'          => [MemberRole::Admin,       true],
        ];
    }

    #[DataProvider('getSheetRightsForEdit')]
    public function testEditAccess(MemberRole $role, bool $isPermitted): void
    {
        $sheet = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateEditFormUrl($sheet->getId()));
        if ($isPermitted) {
            static::assertResponseIsSuccessful();
        } else {
            static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    #[DataProvider('getSheetRightsForEdit')]
    public function testEditDisplayOnIndex(MemberRole $role, bool $isPermitted): void
    {
        $sheet = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());
        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::EDIT, $sheet->getId() ?? '');
        } else {
            self::assertIndexEntityActionNotExists(Action::EDIT, $sheet->getId() ?? '');
        }
    }

    /**
     * @return iterable<string, array{MemberRole, bool, bool}>
     */
    public static function getSheetRightsForDelete(): iterable
    {
        return [
            'Member cannot delete sheets'      => [MemberRole::Member,      false, true],
            'Contributor cannot delete sheets' => [MemberRole::Contributor, false, true],
            'Librarian can delete sheets'      => [MemberRole::Librarian,   true,  true],
            'Admin can delete sheets'          => [MemberRole::Admin,       true,  true],
        ];
    }

    #[DataProvider('getSheetRightsForDelete')]
    public function testDeleteAccess(MemberRole $role, bool $isPermitted, bool $canViewIndex): void
    {
        $sheet = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $this->loginAs($role);
        $this->client->request('GET', $this->generateIndexUrl());

        if ($isPermitted) {
            self::assertIndexEntityActionExists(Action::DELETE, $sheet->getId() ?? '');
        } elseif ($canViewIndex) {
            self::assertIndexEntityActionNotExists(Action::DELETE, $sheet->getId() ?? '');
        }
    }

    public function testExportAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->getCrudUrl('export'));
        static::assertResponseRedirects('/login');
    }

    public function testExportReturnsCsvForAuthenticatedUser(): void
    {
        $this->loginAs(MemberRole::Member);
        $this->client->request('GET', $this->getCrudUrl('export'));

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'text/csv; charset=UTF-8');
        static::assertStringContainsString(
            'attachment; filename="sheets-',
            $this->client->getResponse()->headers->get('Content-Disposition') ?? ''
        );
    }

    public function testExportCsvContainsHeaderRow(): void
    {
        $this->loginAs(MemberRole::Member);
        $this->client->request('GET', $this->getCrudUrl('export'));

        $content = $this->client->getInternalResponse()->getContent();
        static::assertStringContainsString('ID', $content);
        static::assertStringContainsString('Title', $content);
        static::assertStringContainsString('Refs', $content);
        static::assertStringContainsString('Credits', $content);
    }
}
