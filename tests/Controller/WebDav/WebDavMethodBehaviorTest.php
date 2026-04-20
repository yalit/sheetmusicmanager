<?php

namespace App\Tests\Controller\WebDav;

use App\DataFixtures\WebDavTokenFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Verifies the contract of each allowed method:
 *  - HEAD  : same status/headers as GET, no response body
 *  - OPTIONS: 200 with DAV compliance header
 *
 * GET and PROPFIND are covered in WebDavSheetTreeTest.
 */
final class WebDavMethodBehaviorTest extends WebTestCase
{
    private function authHeaders(): array
    {
        return [
            'PHP_AUTH_USER' => 'admin@sheetmusic.test',
            'PHP_AUTH_PW'   => WebDavTokenFixtures::ADMIN_PLAIN_TOKEN,
        ];
    }

    // -------------------------------------------------------------------------
    // HEAD
    // -------------------------------------------------------------------------

    public function testHeadOnDirectoryReturns200WithNoBody(): void
    {
        $client = static::createClient();
        $client->request('HEAD', '/dav/sheets/', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(200);
        static::assertEmpty($client->getResponse()->getContent());
    }

    public function testHeadOnExistingFileReturns200WithPdfContentTypeAndNoBody(): void
    {
        $client = static::createClient();
        // untagged_file.pdf is a committed test fixture — no temp file needed
        $client->request('HEAD', '/dav/sheets/_Untagged/Untagged.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame('Content-Type', 'application/pdf');
        static::assertEmpty($client->getResponse()->getContent());
    }

    public function testHeadOnMissingFileReturns404(): void
    {
        $client = static::createClient();
        $client->request('HEAD', '/dav/sheets/piano/nonexistent-piece.pdf', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // OPTIONS
    // -------------------------------------------------------------------------

    public function testOptionsReturns200(): void
    {
        $client = static::createClient();
        $client->request('OPTIONS', '/dav/', [], [], $this->authHeaders());

        static::assertResponseStatusCodeSame(200);
    }

    public function testOptionsResponseIncludesDavComplianceHeader(): void
    {
        $client = static::createClient();
        $client->request('OPTIONS', '/dav/', [], [], $this->authHeaders());

        static::assertResponseHasHeader('DAV');
    }
}
