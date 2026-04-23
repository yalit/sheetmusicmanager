<?php

namespace App\Tests\Controller\Action;

use App\Entity\Security\Member;
use App\Entity\Setlist\Setlist;
use App\Enum\Security\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExportSetlistToForScoreControllerTest extends WebTestCase
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

    private function getSetlist(string $title): Setlist
    {
        $setlist = $this->em()->getRepository(Setlist::class)->findOneBy(['title' => $title]);
        static::assertNotNull($setlist, "Fixture setlist '{$title}' not found");
        return $setlist;
    }

    private function actionUrl(Setlist $setlist): string
    {
        return static::getContainer()->get('router')->generate('admin_export_setlist_forscore', ['id' => $setlist->getId()]);
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        static::assertResponseRedirects('/login');
    }

    public function testMemberCannotExportSetlistTheyDoNotOwn(): void
    {
        $this->loginAs(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        static::assertResponseStatusCodeSame(403);
    }

    public function testContributorCanExportAnySetlist(): void
    {
        $this->loginAs(MemberRole::Contributor);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        static::assertResponseIsSuccessful();
    }

    public function testMemberCanExportTheirOwnSetlist(): void
    {
        $this->loginAs(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Member Setlist')));

        static::assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // Response format
    // -------------------------------------------------------------------------

    public function testResponseIsXmlWithCorrectContentType(): void
    {
        $this->loginAs(MemberRole::Admin);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'application/xml');
    }

    public function testResponseHasCorrectContentDisposition(): void
    {
        $this->loginAs(MemberRole::Admin);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        static::assertResponseHeaderSame('Content-Disposition', 'attachment; filename="Admin Setlist.4ss"');
    }

    // -------------------------------------------------------------------------
    // XML content
    // -------------------------------------------------------------------------

    public function testExportedXmlHasCorrectRootAttributes(): void
    {
        $this->loginAs(MemberRole::Admin);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        static::assertNotFalse($xml);
        static::assertSame('forScore', $xml->getName());
        static::assertSame('setlist', (string) $xml['kind']);
        static::assertSame('1.0', (string) $xml['version']);
        static::assertSame('Admin Setlist', (string) $xml['title']);
    }

    public function testSetlistWithAllFilesOnDiskProducesOnlyScoreElements(): void
    {
        // Admin Setlist uses sheets 7, 5, 10, 3 — all present in tests/public/uploads/sheets/
        $this->loginAs(MemberRole::Admin);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        static::assertNotFalse($xml);
        static::assertSame(4, count($xml->score));
        static::assertSame(0, count($xml->placeholder));
    }

    public function testSetlistWithMissingFileProducesPlaceholderElement(): void
    {
        // 'Missing files Setlist' has one item using a sheet with no file on disk
        $this->loginAs(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Missing files Setlist')));

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        static::assertNotFalse($xml);
        static::assertSame(0, count($xml->score));
        static::assertSame(1, count($xml->placeholder));
    }

    public function testEmptySetlistProducesNoEntries(): void
    {
        $this->loginAs(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Empty Setlist')));

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        static::assertNotFalse($xml);
        static::assertSame(0, count($xml->children()));
    }

    public function testScorePathMatchesSetlistFilename(): void
    {
        $this->loginAs(MemberRole::Admin);

        $this->client->request('GET', $this->actionUrl($this->getSetlist('Admin Setlist')));

        $xml   = simplexml_load_string($this->client->getResponse()->getContent());
        static::assertNotFalse($xml);
        $paths = [];
        foreach ($xml->score as $score) {
            $paths[] = (string) $score['path'];
        }

        // Paths are sheet titles (matching the /dav/sheets/ branch), not setlist item names
        static::assertContains('Messiah — Hallelujah Chorus.pdf', $paths);
        static::assertContains('Requiem in D Minor.pdf', $paths);
        static::assertContains('Cantique de Jean Racine.pdf', $paths);
        static::assertContains("Jesu, Joy of Man's Desiring.pdf", $paths);
    }
}
