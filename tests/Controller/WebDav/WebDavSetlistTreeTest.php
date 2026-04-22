<?php

namespace App\Tests\Controller\WebDav;

use App\DataFixtures\WebDavTokenFixtures;
use DOMDocument;
use DOMXPath;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebDavSetlistTreeTest extends WebTestCase
{
    private array $filesToCleanup = [];
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        foreach ($this->filesToCleanup as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authHeaders(): array
    {
        return [
            'PHP_AUTH_USER' => 'admin@sheetmusic.test',
            'PHP_AUTH_PW'   => WebDavTokenFixtures::ADMIN_PLAIN_TOKEN,
        ];
    }

    /**
     * Makes a PROPFIND request and returns decoded DAV:href values from the 207 response.
     *
     * @return string[]
     */
    private function propfindHrefs(string $path, int $depth = 1): array
    {
        $this->client->request('PROPFIND', $path, [], [], array_merge(
            $this->authHeaders(),
            ['HTTP_DEPTH' => (string) $depth]
        ));

        static::assertResponseStatusCodeSame(207);

        $dom = new DOMDocument();
        $dom->loadXML($this->client->getResponse()->getContent());

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('d', 'DAV:');

        $hrefs = [];
        foreach ($xpath->query('//d:href') as $node) {
            $hrefs[] = rawurldecode($node->textContent);
        }

        return $hrefs;
    }

    private function createRealPdfFile(string $filename): string
    {
        $uploadDir = static::getContainer()->getParameter('kernel.project_dir')
            . '/tests/public/uploads/sheets';

        $path = $uploadDir . '/' . $filename;
        file_put_contents($path, '%PDF-1.4 fake pdf content for ' . $filename);
        $this->filesToCleanup[] = $path;

        return $path;
    }

    // -------------------------------------------------------------------------
    // Root
    // -------------------------------------------------------------------------

    public function testRootListsSetlistsDirectory(): void
    {
        $hrefs = $this->propfindHrefs('/dav/');

        static::assertContains('/dav/setlists/', $hrefs);
    }

    // -------------------------------------------------------------------------
    // PROPFIND — directory structure
    // -------------------------------------------------------------------------

    public function testSetlistsRootListsAllSetlistFolders(): void
    {
        $hrefs = $this->propfindHrefs('/dav/setlists/');

        static::assertContains('/dav/setlists/Member Setlist/', $hrefs);
        static::assertContains('/dav/setlists/Contributor Setlist/', $hrefs);
        static::assertContains('/dav/setlists/Librarian Setlist/', $hrefs);
        static::assertContains('/dav/setlists/Admin Setlist/', $hrefs);
        static::assertContains('/dav/setlists/Empty Setlist/', $hrefs);
        static::assertContains('/dav/setlists/Missing files Setlist/', $hrefs);
    }

    public function testSetlistDirectoryListsFilesWithPositionPrefix(): void
    {
        $hrefs = $this->propfindHrefs('/dav/setlists/Admin Setlist/');
        $files = array_values(array_filter($hrefs, fn(string $h) => str_ends_with($h, '.pdf')));

        static::assertCount(4, $files);
        static::assertContains('/dav/setlists/Admin Setlist/01 - Entrée.pdf', $files);
        static::assertContains('/dav/setlists/Admin Setlist/02 - Requiem.pdf', $files);
        static::assertContains('/dav/setlists/Admin Setlist/03 - Cantique.pdf', $files);
        static::assertContains('/dav/setlists/Admin Setlist/04 - Sortie.pdf', $files);
    }

    public function testEmptySetlistDirectoryListsNoFiles(): void
    {
        $hrefs = $this->propfindHrefs('/dav/setlists/Empty Setlist/');
        $files = array_values(array_filter($hrefs, fn(string $h) => str_ends_with($h, '.pdf')));

        static::assertCount(0, $files);
    }

    public function testMissingFileSetlistShowsNoFileMarkerInFilename(): void
    {
        $hrefs = $this->propfindHrefs('/dav/setlists/Missing files Setlist/');
        $files = array_values(array_filter($hrefs, fn(string $h) => str_ends_with($h, '.pdf')));

        static::assertCount(1, $files);
        static::assertContains('/dav/setlists/Missing files Setlist/01 - Entrée - NOFILE -.pdf', $files);
    }

    // -------------------------------------------------------------------------
    // GET
    // -------------------------------------------------------------------------

    public function testGetSetlistFileReturnsCorrectContent(): void
    {
        // Librarian Setlist position 1 = 'Prélude' → underlying file sheet-8.pdf (Clair de Lune)
        $expectedContent = '%PDF-1.4 fake pdf content for sheet-8.pdf';
        $this->createRealPdfFile('sheet-8.pdf');

        $this->client->request('GET', '/dav/setlists/Librarian Setlist/01 - Prélude.pdf', [], [], $this->authHeaders());

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'application/pdf');
        static::assertSame($expectedContent, $this->client->getResponse()->getContent());
    }

    public function testGetSetlistFileWithMissingPdfReturns404(): void
    {
        $this->client->request('GET', '/dav/setlists/Missing files Setlist/01 - Entrée - NOFILE -.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    public function testGetNonExistentSetlistFolderReturns404(): void
    {
        $this->client->request('GET', '/dav/setlists/Nonexistent Setlist/', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    public function testGetNonExistentFileInSetlistReturns404(): void
    {
        $this->client->request('GET', '/dav/setlists/Admin Setlist/99 - Ghost.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }
}
