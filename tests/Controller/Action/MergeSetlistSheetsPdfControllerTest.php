<?php

namespace App\Tests\Controller\Action;

use App\Entity\Member;
use App\Entity\SetListItem;
use App\Entity\Setlist;
use App\Entity\Sheet;
use App\Entity\ValueObject\StoredFile;
use App\Enum\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MergeSetlistSheetsPdfControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $testFile;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        if (isset($this->testFile) && file_exists($this->testFile)) {
            unlink($this->testFile);
        }

        parent::tearDown();
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

    private function getSetlist(MemberRole $owner): Setlist
    {
        $title   = $owner->name . ' Setlist';
        $setlist = $this->em()->getRepository(Setlist::class)->findOneBy(['title' => $title]);
        static::assertNotNull($setlist, "Fixture setlist '{$title}' not found");
        return $setlist;
    }

    private function actionUrl(Setlist $setlist): string
    {
        return static::getContainer()->get('router')->generate('admin_merge_setlist_sheets_pdf', ['id' => $setlist->getId()]);
    }

    private function createRealFileInUploadDir(string $filename): string
    {
        $uploadDir = static::getContainer()->getParameter('kernel.project_dir')
            . DIRECTORY_SEPARATOR . 'tests'
            . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'sheets';

        $path = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, '%PDF-1.4 fake pdf content');

        return $path;
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testAnonymousRedirectsToLogin(): void
    {
        $setlist = $this->getSetlist(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseRedirects('/login');
    }

    // -------------------------------------------------------------------------
    // Empty setlist (no items) → warning flash + redirect
    // -------------------------------------------------------------------------

    public function testSetlistWithNoItemsRedirectsWithWarning(): void
    {
        $this->loginAs(MemberRole::Librarian);
        $setlist = $this->getSetlist(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseRedirects();
        $this->client->followRedirect();
        static::assertSelectorExists('.alert-warning');
    }

    // -------------------------------------------------------------------------
    // Setlist with items but no files on disk → same warning redirect
    // -------------------------------------------------------------------------

    public function testSetlistWithMissingFilesRedirectsWithWarning(): void
    {
        $this->loginAs(MemberRole::Librarian);

        $em = $this->em();

        $sheet = (new Sheet())->setTitle('Missing File Sheet')->setFiles([
            new StoredFile('missing.pdf', 'missing_file_that_does_not_exist.pdf'),
        ]);
        $em->persist($sheet);

        $setlist = $this->getSetlist(MemberRole::Librarian);
        $item    = (new SetListItem())->setPosition(1)->setName('')->setNotes('')->setSheet($sheet);
        $setlist->addItem($item);
        $em->flush();

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseRedirects();
        $this->client->followRedirect();
        static::assertSelectorExists('.alert-warning');
    }

    // -------------------------------------------------------------------------
    // Setlist with a real PDF file on disk → 200 attachment
    // -------------------------------------------------------------------------

    public function testSetlistWithRealFilesReturnsPdfAttachment(): void
    {
        $this->loginAs(MemberRole::Librarian);

        $filename        = 'test_merge_' . uniqid() . '.pdf';
        $this->testFile  = $this->createRealFileInUploadDir($filename);

        $em = $this->em();

        $sheet = (new Sheet())->setTitle('Real File Sheet')->setFiles([
            new StoredFile('real.pdf', $filename),
        ]);
        $em->persist($sheet);

        $setlist = $this->getSetlist(MemberRole::Admin);
        $item    = (new SetListItem())->setPosition(1)->setName('')->setNotes('')->setSheet($sheet);
        $setlist->addItem($item);
        $em->flush();

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame(
            'Content-Disposition',
            sprintf('attachment; filename="partitions-%s"', $setlist->getId()),
        );
    }
}
