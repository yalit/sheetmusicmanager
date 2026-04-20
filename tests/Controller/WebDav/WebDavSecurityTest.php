<?php

namespace App\Tests\Controller\WebDav;

use App\DataFixtures\WebDavTokenFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebDavSecurityTest extends WebTestCase
{
    private const DAV_URL = '/dav/sheets/_Untagged/Untagged.pdf';

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function requestDav(string $email, string $plainToken): int
    {
        $client = static::createClient();
        $client->request('GET', self::DAV_URL, [], [], [
            'PHP_AUTH_USER' => $email,
            'PHP_AUTH_PW'   => $plainToken,
        ]);

        return $client->getResponse()->getStatusCode();
    }

    // -------------------------------------------------------------------------
    // Blocked access (must return 401)
    // -------------------------------------------------------------------------

    public function testAnonymousRequestIsRejected(): void
    {
        $client = static::createClient();
        $client->request('GET', self::DAV_URL);

        static::assertResponseStatusCodeSame(401);
    }

    public function testWrongTokenIsRejected(): void
    {
        $status = $this->requestDav('admin@sheetmusic.test', 'wrong-token');

        static::assertSame(401, $status);
    }

    public function testMemberWithNoTokenIsRejected(): void
    {
        $status = $this->requestDav('member@sheetmusic.test', 'any-token');

        static::assertSame(401, $status);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $status = $this->requestDav('expired@sheetmusic.test', 'expired-plain-webdav-token');

        static::assertSame(401, $status);
    }

    // -------------------------------------------------------------------------
    // Allowed access (must not return 401)
    // -------------------------------------------------------------------------

    public function testValidAdminTokenIsAccepted(): void
    {
        $status = $this->requestDav('admin@sheetmusic.test', WebDavTokenFixtures::ADMIN_PLAIN_TOKEN);

        static::assertSame(200, $status);
    }

    public function testValidLibrarianTokenIsAccepted(): void
    {
        $status = $this->requestDav('librarian@sheetmusic.test', WebDavTokenFixtures::LIBRARIAN_PLAIN_TOKEN);

        static::assertSame(200, $status);
    }

    public function testValidContributorTokenIsAccepted(): void
    {
        $status = $this->requestDav('contributor@sheetmusic.test', WebDavTokenFixtures::CONTRIBUTOR_PLAIN_TOKEN);

        static::assertSame(200, $status);
    }
}
