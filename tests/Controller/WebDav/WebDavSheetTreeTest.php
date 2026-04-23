<?php

namespace App\Tests\Controller\WebDav;

use App\DataFixtures\WebDavTokenFixtures;
use App\Entity\Sheet\Sheet;
use App\Entity\Sheet\ValueObject\StoredFile;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebDavSheetTreeTest extends WebTestCase
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

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function authHeaders(): array
    {
        return [
            'PHP_AUTH_USER' => 'admin@sheetmusic.test',
            'PHP_AUTH_PW'   => WebDavTokenFixtures::ADMIN_PLAIN_TOKEN,
        ];
    }

    /**
     * Makes a PROPFIND request and returns the list of DAV:href values from the 207 response.
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

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('d', 'DAV:');

        $hrefs = [];
        foreach ($xpath->query('//d:href') as $node) {
            $hrefs[] = $node->textContent;
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
    // PROPFIND — directory structure
    // -------------------------------------------------------------------------

    public function testRootListsSheetsDirectory(): void
    {
        $hrefs = $this->propfindHrefs('/dav/');

        static::assertContains('/dav/sheets/', $hrefs);
    }

    public function testSheetsRootListsAllTagFolders(): void
    {
        $hrefs = $this->propfindHrefs('/dav/sheets/');

        // Derived from SheetFixtures::SHEETS — distinct first tags
        static::assertContains('/dav/sheets/organ/', $hrefs);
        static::assertContains('/dav/sheets/orchestra/', $hrefs);
        static::assertContains('/dav/sheets/choir/', $hrefs);
        static::assertContains('/dav/sheets/strings/', $hrefs);
        static::assertContains('/dav/sheets/piano/', $hrefs);
        static::assertContains('/dav/sheets/_Untagged/', $hrefs);
    }

    public function testTagFolderListsItsSheets(): void
    {
        $hrefs = $this->propfindHrefs('/dav/sheets/piano/');
        $files = array_map(fn(string $s) => rawurldecode($s), array_values(array_filter($hrefs, fn(string $h) => str_ends_with($h, '.pdf'))));

        // SheetFixtures: piano sheets are SHEET_8, 9, 12, 13
        // 1 of the piano related sheet is the one missing a file on disk
        static::assertCount(4, $files);
        static::assertContains('/dav/sheets/piano/Clair de Lune.pdf', $files);
        static::assertContains('/dav/sheets/piano/Gymnopédie No. 1.pdf', $files);
        static::assertContains('/dav/sheets/piano/Nocturne in E flat Major.pdf', $files);
        static::assertContains('/dav/sheets/piano/Liebestraum No. 3.pdf', $files);
    }

    public function testUntaggedFolderListsSheetsWithNoTags(): void
    {
        $hrefs = $this->propfindHrefs('/dav/sheets/_Untagged/');
        $files = array_map(fn(string $s) => rawurldecode($s),array_values(array_filter($hrefs, fn(string $h) => str_ends_with($h, '.pdf'))));

        // SheetFixtures: 'No file on the drive' has empty tags []
        static::assertCount(1, $files);
        static::assertContains('/dav/sheets/_Untagged/Untagged.pdf', $files);
    }

    // -------------------------------------------------------------------------
    // GET — single PDF
    // -------------------------------------------------------------------------

    public function testGetSinglePdfReturnsCorrectContent(): void
    {
        // sheet-8 is 'Clair de Lune' (fixture filename: sheet-8.pdf)
        $this->client->request('GET', '/dav/sheets/piano/Clair de Lune.pdf', [], [], $this->authHeaders());

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testGetSheetWithMissingFileOnDiskReturns404(): void
    {
        // 'No file on the drive' has a StoredFile record but missing_file.pdf is not on disk
        $this->client->request('GET', '/dav/sheets/piano/No file on the drive.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    public function testGetNonExistentFileReturns404(): void
    {
        $this->client->request('GET', '/dav/sheets/piano/nonexistent-piece.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    public function testGetNonExistentTagFolderReturns404(): void
    {
        $this->client->request('GET', '/dav/sheets/harpsichord/', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // GET — multiple PDFs (merged via Gotenberg)
    // -------------------------------------------------------------------------

    public function testGetSheetWithMultiplePdfsReturnsMergedPdf(): void
    {
        $file1Content = '%PDF-1.4 fake first page';
        $file2Content = '%PDF-1.4 fake second page';

        $file1 = 'dav_multi_1_' . uniqid() . '.pdf';
        $file2 = 'dav_multi_2_' . uniqid() . '.pdf';

        $path1 = $this->createRealPdfFile($file1);
        $path2 = $this->createRealPdfFile($file2);

        file_put_contents($path1, $file1Content);
        file_put_contents($path2, $file2Content);

        $sheet = (new Sheet())
            ->setTitle('Multi PDF Sheet')
            ->setTags(['piano'])
            ->setFiles([
                new StoredFile('first.pdf', $file1),
                new StoredFile('second.pdf', $file2),
            ]);

        $this->em()->persist($sheet);
        $this->em()->flush();

        $this->client->request('GET', '/dav/sheets/piano/Multi PDF Sheet.pdf', [], [], $this->authHeaders());

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'application/pdf');
        // Content is produced by the Gotenberg merge — assert it is non-empty PDF
        static::assertStringStartsWith('%PDF', $this->client->getResponse()->getContent());
    }
}
