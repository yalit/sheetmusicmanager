<?php

namespace App\Tests\Controller\Action;

use App\DataFixtures\SheetFixtures;
use App\Entity\Member;
use App\Entity\Setlist;
use App\Entity\Sheet;
use App\Enum\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * E2E tests for the "Add sheets to setlist" batch action controller.
 *
 * The action uses a two-POST flow:
 *   1. EA batch POST with batchActionEntityIds[] → renders the setlist-picker form.
 *   2. Form submission POST → dispatches the message, redirects to the setlist edit page.
 */
final class AddToSetlistControllerTest extends WebTestCase
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

    private function loginAs(MemberRole $role): void
    {
        $email  = "{$role->value}@sheetmusic.test";
        $member = $this->em()->getRepository(Member::class)->findOneBy(['email' => $email]);
        static::assertNotNull($member, "Fixture member '{$email}' not found");
        $this->client->loginUser($member);
    }

    private function getSheet(string $title): Sheet
    {
        $sheet = $this->em()->getRepository(Sheet::class)->findOneBy(['title' => $title]);
        static::assertNotNull($sheet, "Fixture sheet '{$title}' not found");
        return $sheet;
    }

    private function getSetlist(MemberRole $owner): Setlist
    {
        $title   = $owner->name . ' Setlist';
        $setlist = $this->em()->getRepository(Setlist::class)->findOneBy(['title' => $title]);
        static::assertNotNull($setlist, "Fixture setlist '{$title}' not found");
        return $setlist;
    }

    private function actionUrl(): string
    {
        return static::getContainer()->get('router')->generate('admin_add_to_setlist');
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testAnonymousRedirectsToLogin(): void
    {
        $this->client->request('POST', $this->actionUrl());
        static::assertResponseRedirects('/login');
    }

    // -------------------------------------------------------------------------
    // Step 1: batch action POST renders the form
    // -------------------------------------------------------------------------

    public function testFirstPostRendersForm(): void
    {
        $this->loginAs(MemberRole::Librarian);
        $sheet1 = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $sheet2 = $this->getSheet(SheetFixtures::SHEETS[1][0]);

        $this->client->request('POST', $this->actionUrl(), [
            'batchActionEntityIds' => [$sheet1->getId(), $sheet2->getId()],
        ]);

        static::assertResponseIsSuccessful();
        static::assertSelectorExists('form');
        static::assertSelectorTextContains('p', '2 fiche(s) sélectionnée(s).');
    }

    // -------------------------------------------------------------------------
    // Step 2: form submission dispatches the message and persists the data
    // -------------------------------------------------------------------------

    public function testFormSubmissionAddsSheetToSetlist(): void
    {
        $this->loginAs(MemberRole::Librarian);
        $sheet1  = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $sheet2  = $this->getSheet(SheetFixtures::SHEETS[1][0]);
        $setlist = $this->getSetlist(MemberRole::Member);

        $initialCount = $setlist->getItems()->count();

        // Step 1: batch POST → renders the form with sheets hidden field pre-filled.
        $this->client->request('POST', $this->actionUrl(), [
            'batchActionEntityIds' => [$sheet1->getId(), $sheet2->getId()],
        ]);
        static::assertResponseIsSuccessful();

        // Step 2: submit the form. The hidden `sheets` field is already populated
        // by the first response; we only need to pick the target setlist.
        $this->client->submitForm('Ajouter', [
            'add_sheets_to_setlist[setlist]' => $setlist->getId(),
        ]);

        static::assertResponseRedirects();

        // Verify the sheets were actually persisted.
        $em = $this->em();
        $em->clear();
        $refreshed = $em->find(Setlist::class, $setlist->getId());
        static::assertCount($initialCount + 2, $refreshed->getItems());
    }

    public function testFormSubmissionRedirectsToSetlistEditPage(): void
    {
        $this->loginAs(MemberRole::Librarian);
        $sheet   = $this->getSheet(SheetFixtures::SHEETS[0][0]);
        $setlist = $this->getSetlist(MemberRole::Member);

        $this->client->request('POST', $this->actionUrl(), [
            'batchActionEntityIds' => [$sheet->getId()],
        ]);

        $this->client->submitForm('Ajouter', [
            'add_sheets_to_setlist[setlist]' => $setlist->getId(),
        ]);

        static::assertResponseRedirects();
        $location = $this->client->getResponse()->headers->get('Location');
        static::assertStringContainsString((string) $setlist->getId(), $location ?? '');
    }
}
